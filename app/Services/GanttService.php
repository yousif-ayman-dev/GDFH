<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GanttService
{
    /**
     * Get normalized Gantt timeline data for a user.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getGanttData(User $user, array $filters = []): array
    {
        // 1. Get user accessible project IDs
        $userTeamIds = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active');
            })
            ->pluck('id');

        $projectsQuery = Project::query()
            ->where(function ($q) use ($user, $userTeamIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereIn('team_id', $userTeamIds)
                  ->orWhereHas('memberRecords', function ($mq) use ($user) {
                      $mq->where('user_id', $user->id)->where('status', 'active');
                  });
            })
            ->with(['owner', 'team', 'tasks' => function ($tq) {
                $tq->with(['assignee', 'creator']);
            }]);

        if (! empty($filters['project_id'])) {
            $projectsQuery->where('id', $filters['project_id']);
        }

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $projectsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        $projects = $projectsQuery->latest()->get();

        $zoom = $filters['zoom'] ?? 'month'; // 'day', 'week', 'month'
        if (! in_array($zoom, ['day', 'week', 'month'], true)) {
            $zoom = 'month';
        }

        // 2. Calculate Timeline View Windows
        $now = Carbon::now();
        $timelineStart = $now->copy()->startOfMonth()->subMonth();
        $timelineEnd = $now->copy()->addMonths(3)->endOfMonth();

        $totalDays = max(30, (int) $timelineStart->diffInDays($timelineEnd) + 1);

        // 3. Process Projects & Tasks Timeline Rows
        $timelineProjects = collect();

        foreach ($projects as $project) {
            $pStart = Carbon::parse($project->start_date ?? $project->created_at);
            $pFinishDate = $project->due_date ?: ($project->deadline ?: null);
            $pFinish = $pFinishDate ? Carbon::parse($pFinishDate) : $pStart->copy()->addDays(30);

            $pDuration = max(1, (int) $pStart->diffInDays($pFinish) + 1);
            $pOffset = (int) $timelineStart->diffInDays($pStart, false);
            $pProgress = $project->progress();
            $pOverdue = $pFinish->isPast() && $project->status !== 'completed';

            $projectTasks = collect();

            foreach ($project->tasks as $task) {
                $tStart = Carbon::parse($task->start_at ?? $task->created_at);
                $tFinish = Carbon::parse($task->due_at ?? $tStart->copy()->addDays(3));

                $tDuration = (int) $tStart->diffInDays($tFinish);
                $tOffset = (int) $timelineStart->diffInDays($tStart, false);
                $isCompleted = in_array($task->status, ['completed', 'done'], true);
                $tProgress = $isCompleted ? 100 : ($task->status === 'in_progress' ? 50 : 0);
                $tOverdue = $tFinish->isPast() && ! $isCompleted;
                $isMilestone = $tDuration === 0;

                $projectTasks->push([
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assignee' => $task->assignee?->name ?? 'غير مُسند',
                    'start_date' => $tStart->format('Y-m-d'),
                    'due_date' => $tFinish->format('Y-m-d'),
                    'duration' => $tDuration,
                    'progress' => $tProgress,
                    'completion' => $isCompleted,
                    'overdue' => $tOverdue,
                    'is_milestone' => $isMilestone,
                    'offset_days' => max(0, $tOffset),
                    'span_days' => max(1, $tDuration),
                    'url' => route('projects.tasks.show', [$project, $task]),
                    'dependencies' => [], // placeholder for dependencies engine
                    'is_critical_path' => $tOverdue || $task->priority === 'urgent',
                ]);
            }

            $timelineProjects->push([
                'id' => $project->id,
                'title' => $project->title,
                'status' => $project->status,
                'owner' => $project->owner?->name,
                'start_date' => $pStart->format('Y-m-d'),
                'finish_date' => $pFinish->format('Y-m-d'),
                'duration' => $pDuration,
                'progress' => $pProgress,
                'overdue' => $pOverdue,
                'offset_days' => max(0, $pOffset),
                'span_days' => max(1, $pDuration),
                'url' => route('projects.show', $project),
                'tasks' => $projectTasks,
            ]);
        }

        // 4. Generate Timeline Column Headers based on Zoom
        $columnHeaders = collect();
        $cursor = $timelineStart->copy();

        if ($zoom === 'day') {
            while ($cursor->lte($timelineEnd)) {
                $columnHeaders->push([
                    'label' => $cursor->format('d M'),
                    'date' => $cursor->format('Y-m-d'),
                    'is_today' => $cursor->isToday(),
                ]);
                $cursor->addDay();
            }
        } elseif ($zoom === 'week') {
            while ($cursor->lte($timelineEnd)) {
                $columnHeaders->push([
                    'label' => 'أسبوع ' . $cursor->format('W (d M)'),
                    'date' => $cursor->format('Y-m-d'),
                    'is_today' => $cursor->isCurrentWeek(),
                ]);
                $cursor->addWeek();
            }
        } else { // month
            while ($cursor->lte($timelineEnd)) {
                $columnHeaders->push([
                    'label' => $cursor->locale('ar')->translatedFormat('F Y'),
                    'date' => $cursor->format('Y-m'),
                    'is_today' => $cursor->isCurrentMonth(),
                ]);
                $cursor->addMonth();
            }
        }

        return [
            'zoom' => $zoom,
            'timeline_start' => $timelineStart->format('Y-m-d'),
            'timeline_end' => $timelineEnd->format('Y-m-d'),
            'total_days' => $totalDays,
            'column_headers' => $columnHeaders,
            'projects' => $timelineProjects,
            'user_projects' => Project::query()->whereIn('id', $projects->pluck('id'))->get(['id', 'title']),
        ];
    }
}
