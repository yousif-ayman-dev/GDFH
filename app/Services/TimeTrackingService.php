<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimeTrackingService
{
    /**
     * Get active timer (running or paused) for user.
     */
    public function getActiveTimer(User $user): ?Worklog
    {
        return Worklog::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['running', 'paused'])
            ->with(['project', 'task'])
            ->latest()
            ->first();
    }

    /**
     * Start a new live timer for user.
     */
    public function startTimer(
        User $user,
        Project $project,
        ?Task $task = null,
        ?string $notes = null,
        bool $isBillable = true
    ): Worklog {
        return DB::transaction(function () use ($user, $project, $task, $notes, $isBillable) {
            // Stop any existing active timer for this user
            if ($activeTimer = $this->getActiveTimer($user)) {
                $this->stopTimer($activeTimer);
            }

            return Worklog::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'task_id' => $task?->id,
                'start_time' => now(),
                'end_time' => null,
                'duration' => 0,
                'status' => 'running',
                'notes' => $notes,
                'is_billable' => $isBillable,
                'is_manual' => false,
            ]);
        });
    }

    /**
     * Pause a running timer.
     */
    public function pauseTimer(Worklog $worklog): Worklog
    {
        if (! $worklog->isRunning()) {
            return $worklog;
        }

        return DB::transaction(function () use ($worklog) {
            $elapsed = (int) now()->diffInSeconds($worklog->start_time);
            $newDuration = $worklog->duration + $elapsed;

            $worklog->update([
                'status' => 'paused',
                'duration' => $newDuration,
                'start_time' => null,
            ]);

            return $worklog->fresh();
        });
    }

    /**
     * Resume a paused timer.
     */
    public function resumeTimer(Worklog $worklog): Worklog
    {
        if (! $worklog->isPaused()) {
            return $worklog;
        }

        return DB::transaction(function () use ($worklog) {
            $worklog->update([
                'status' => 'running',
                'start_time' => now(),
            ]);

            return $worklog->fresh();
        });
    }

    /**
     * Stop a timer.
     */
    public function stopTimer(Worklog $worklog, ?string $notes = null): Worklog
    {
        if (in_array($worklog->status, ['stopped', 'manual'], true)) {
            return $worklog;
        }

        return DB::transaction(function () use ($worklog, $notes) {
            $accumulated = $worklog->duration;

            if ($worklog->isRunning() && $worklog->start_time) {
                $elapsed = (int) now()->diffInSeconds($worklog->start_time);
                $accumulated += $elapsed;
            }

            $updateData = [
                'status' => 'stopped',
                'end_time' => now(),
                'duration' => $accumulated,
                'start_time' => $worklog->start_time ?? now()->subSeconds($accumulated),
            ];

            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }

            $worklog->update($updateData);

            return $worklog->fresh();
        });
    }

    /**
     * Create a manual worklog entry.
     */
    public function createManualWorklog(
        User $user,
        Project $project,
        ?Task $task,
        int $durationMinutes,
        ?string $notes = null,
        bool $isBillable = true,
        ?string $date = null
    ): Worklog {
        $logDate = $date ? Carbon::parse($date) : now();
        $seconds = max(60, $durationMinutes * 60);

        return Worklog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_id' => $task?->id,
            'start_time' => $logDate->copy()->subSeconds($seconds),
            'end_time' => $logDate,
            'duration' => $seconds,
            'status' => 'manual',
            'notes' => $notes,
            'is_billable' => $isBillable,
            'is_manual' => true,
        ]);
    }

    /**
     * Update an existing worklog.
     */
    public function updateWorklog(Worklog $worklog, array $data): Worklog
    {
        if (isset($data['duration_minutes'])) {
            $data['duration'] = max(60, (int) $data['duration_minutes'] * 60);
            unset($data['duration_minutes']);
        }

        $worklog->update($data);

        return $worklog->fresh();
    }

    /**
     * Delete a worklog.
     */
    public function deleteWorklog(Worklog $worklog): void
    {
        $worklog->delete();
    }

    /**
     * Total tracked time in seconds for a user.
     */
    public function totalTrackedTime(User $user): int
    {
        return (int) Worklog::where('user_id', $user->id)->sum('duration');
    }

    /**
     * Total tracked time in seconds for a user (alias).
     */
    public function userTrackedTime(User $user): int
    {
        return $this->totalTrackedTime($user);
    }

    /**
     * Project tracked time in seconds.
     */
    public function projectTrackedTime(Project $project): int
    {
        return (int) Worklog::where('project_id', $project->id)->sum('duration');
    }

    /**
     * Task tracked time in seconds.
     */
    public function taskTrackedTime(Task $task): int
    {
        return (int) Worklog::where('task_id', $task->id)->sum('duration');
    }

    /**
     * Weekly time tracking summary for user.
     *
     * @return array<string, mixed>
     */
    public function weeklySummary(User $user): array
    {
        $startOfWeek = now()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = now()->endOfWeek(Carbon::SATURDAY);

        $logs = Worklog::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $totalSeconds = $logs->sum('duration');
        $billableSeconds = $logs->where('is_billable', true)->sum('duration');

        return [
            'total_seconds' => $totalSeconds,
            'total_hours' => round($totalSeconds / 3600, 1),
            'billable_hours' => round($billableSeconds / 3600, 1),
            'logs_count' => $logs->count(),
            'billable_percentage' => $totalSeconds > 0 ? (int) round(($billableSeconds / $totalSeconds) * 100) : 0,
        ];
    }

    /**
     * Monthly time tracking summary for user.
     *
     * @return array<string, mixed>
     */
    public function monthlySummary(User $user): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $logs = Worklog::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $totalSeconds = $logs->sum('duration');
        $billableSeconds = $logs->where('is_billable', true)->sum('duration');

        return [
            'total_seconds' => $totalSeconds,
            'total_hours' => round($totalSeconds / 3600, 1),
            'billable_hours' => round($billableSeconds / 3600, 1),
            'logs_count' => $logs->count(),
            'billable_percentage' => $totalSeconds > 0 ? (int) round(($billableSeconds / $totalSeconds) * 100) : 0,
        ];
    }

    /**
     * Aggregate productivity statistics.
     *
     * @return array<string, mixed>
     */
    public function getAnalytics(User $user): array
    {
        $todaySeconds = (int) Worklog::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->sum('duration');

        $totalSeconds = $this->totalTrackedTime($user);
        $weekly = $this->weeklySummary($user);
        $monthly = $this->monthlySummary($user);

        // Average task duration
        $taskLogs = Worklog::query()
            ->where('user_id', $user->id)
            ->whereNotNull('task_id')
            ->where('duration', '>', 0)
            ->get();

        $avgTaskSeconds = $taskLogs->count() > 0 ? (int) round($taskLogs->avg('duration')) : 0;

        return [
            'total_hours' => round($totalSeconds / 3600, 1),
            'today_hours' => round($todaySeconds / 3600, 1),
            'weekly_hours' => $weekly['total_hours'],
            'monthly_hours' => $monthly['total_hours'],
            'billable_percentage' => $weekly['billable_percentage'],
            'avg_task_duration_minutes' => (int) round($avgTaskSeconds / 60),
        ];
    }
}
