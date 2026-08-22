<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Get accessible projects for user.
     */
    public function getUserProjects(User $user): Collection
    {
        $userTeamIds = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active');
            })
            ->pluck('id');

        return Project::query()
            ->where(function ($q) use ($user, $userTeamIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereIn('team_id', $userTeamIds)
                  ->orWhereHas('memberRecords', function ($mq) use ($user) {
                      $mq->where('user_id', $user->id)->where('status', 'active');
                  });
            })
            ->get(['id', 'title']);
    }

    /**
     * Get normalized calendar events for a user based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function getEvents(User $user, array $filters = []): Collection
    {
        // 1. Get accessible projects & teams for user
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
            ->with(['owner', 'team']);

        // Apply project filter if specified
        if (! empty($filters['project_id'])) {
            $projectsQuery->where('id', $filters['project_id']);
        }

        $projects = $projectsQuery->get();
        $projectIds = $projects->pluck('id');

        // 2. Query tasks
        $tasksQuery = Task::query()
            ->whereIn('project_id', $projectIds)
            ->with(['project', 'assignee', 'creator']);

        if (! empty($filters['assigned_to_me'])) {
            $tasksQuery->where('assigned_to', $user->id);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $tasksQuery->whereIn('status', ['completed', 'done']);
            } elseif ($filters['status'] === 'open') {
                $tasksQuery->whereNotIn('status', ['completed', 'done', 'cancelled']);
            }
        }

        if (! empty($filters['overdue'])) {
            $tasksQuery->whereNotIn('status', ['completed', 'done', 'cancelled'])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now());
        }

        $tasks = $tasksQuery->get();

        $events = collect();

        // 3. Process Project Events (unless type == 'task')
        if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'project') {
            foreach ($projects as $project) {
                // Project Start Date
                $startDate = $project->start_date ?? $project->created_at;
                if ($startDate) {
                    $events->push($this->formatEvent([
                        'id' => 'project-start-' . $project->id,
                        'title' => 'بداية مشروع: ' . $project->title,
                        'type' => 'project_start',
                        'status' => $project->status,
                        'owner' => $project->owner,
                        'date' => Carbon::parse($startDate)->format('Y-m-d'),
                        'datetime' => Carbon::parse($startDate),
                        'related_project' => $project,
                        'related_team' => $project->team,
                        'priority' => 'medium',
                        'color_category' => 'blue',
                        'url' => route('projects.show', $project),
                    ]));
                }

                // Project Deadline/Due Date
                $dueDate = $project->due_date ?? $project->deadline;
                if ($dueDate) {
                    $isOverdue = Carbon::parse($dueDate)->isPast() && $project->status !== 'completed';
                    $events->push($this->formatEvent([
                        'id' => 'project-due-' . $project->id,
                        'title' => 'تسليم مشروع: ' . $project->title,
                        'type' => 'project_due',
                        'status' => $project->status,
                        'owner' => $project->owner,
                        'date' => Carbon::parse($dueDate)->format('Y-m-d'),
                        'datetime' => Carbon::parse($dueDate),
                        'related_project' => $project,
                        'related_team' => $project->team,
                        'priority' => 'high',
                        'color_category' => $isOverdue ? 'red' : 'amber',
                        'url' => route('projects.show', $project),
                    ]));
                }
            }
        }

        // 4. Process Task Events (unless type == 'project')
        if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'task') {
            foreach ($tasks as $task) {
                if ($task->due_at) {
                    $isCompleted = in_array($task->status, ['completed', 'done'], true);
                    $isOverdue = Carbon::parse($task->due_at)->isPast() && ! $isCompleted;

                    $color = $isCompleted ? 'emerald' : ($isOverdue ? 'red' : ($task->priority === 'urgent' ? 'purple' : 'copper'));

                    $events->push($this->formatEvent([
                        'id' => 'task-due-' . $task->id,
                        'title' => 'مهمة: ' . $task->title,
                        'type' => 'task_due',
                        'status' => $task->status,
                        'owner' => $task->assignee ?? $task->creator,
                        'date' => Carbon::parse($task->due_at)->format('Y-m-d'),
                        'datetime' => Carbon::parse($task->due_at),
                        'related_project' => $task->project,
                        'related_team' => $task->project?->team,
                        'priority' => $task->priority,
                        'color_category' => $color,
                        'url' => route('projects.tasks.show', [$task->project, $task]),
                    ]));
                }
            }
        }

        // 5. Process Custom User Events (unless type == 'project' or 'task')
        if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'event' || $filters['type'] === 'custom') {
            $userEventsQuery = \App\Models\Event::query()
                ->where(function ($q) use ($user, $projectIds) {
                    $q->where('user_id', $user->id)
                      ->orWhereIn('project_id', $projectIds);
                })
                ->with(['user', 'project']);

            if (! empty($filters['project_id'])) {
                $userEventsQuery->where('project_id', $filters['project_id']);
            }

            if (! empty($filters['assigned_to_me'])) {
                $userEventsQuery->where('user_id', $user->id);
            }

            if (! empty($filters['overdue'])) {
                $userEventsQuery->where('start_at', '<', now());
            }

            foreach ($userEventsQuery->get() as $customEvent) {
                $events->push($this->formatEvent([
                    'id' => 'custom-event-' . $customEvent->id,
                    'db_id' => $customEvent->id,
                    'title' => $customEvent->title,
                    'description' => $customEvent->description,
                    'type' => 'custom_event',
                    'status' => 'active',
                    'owner' => $customEvent->user,
                    'date' => Carbon::parse($customEvent->start_at)->format('Y-m-d'),
                    'datetime' => Carbon::parse($customEvent->start_at),
                    'end_at' => $customEvent->end_at ? Carbon::parse($customEvent->end_at)->format('Y-m-d H:i') : null,
                    'related_project' => $customEvent->project,
                    'related_team' => $customEvent->project?->team,
                    'priority' => 'normal',
                    'color_category' => $customEvent->color ?? 'copper',
                    'location' => $customEvent->location,
                    'is_editable' => $user->id === $customEvent->user_id,
                    'url' => '#',
                ]));
            }
        }

        // Apply month range filter if month is provided (e.g., '2026-08')
        if (! empty($filters['month'])) {
            $month = Carbon::parse($filters['month'] . '-01');
            $startOfMonth = $month->copy()->startOfMonth()->format('Y-m-d');
            $endOfMonth = $month->copy()->endOfMonth()->format('Y-m-d');

            $events = $events->filter(function ($evt) use ($startOfMonth, $endOfMonth) {
                return $evt['date'] >= $startOfMonth && $evt['date'] <= $endOfMonth;
            });
        }

        return $events->sortBy('datetime')->values();
    }

    /**
     * Build month calendar grid (28 to 35 days) with grouped events.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getCalendarGrid(User $user, ?string $yearMonth = null, array $filters = []): array
    {
        $currentMonth = $yearMonth ? Carbon::parse($yearMonth . '-01') : Carbon::now();
        $filters['month'] = $currentMonth->format('Y-m');

        $events = $this->getEvents($user, $filters);
        $eventsByDate = $events->groupBy('date');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Carbon week starts on Sunday/Monday. Let's build full grid days
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $days = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $dateStr = $cursor->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'day_number' => $cursor->day,
                'is_current_month' => $cursor->month === $currentMonth->month,
                'is_today' => $cursor->isToday(),
                'events' => $eventsByDate->get($dateStr, collect()),
            ];
            $cursor->addDay();
        }

        return [
            'current_month' => $currentMonth,
            'prev_month' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'next_month' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'events_count' => $events->count(),
        ];
    }

    /**
     * Build weekly schedule (7 days from start date).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getWeeklySchedule(User $user, ?string $startDate = null, array $filters = []): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfWeek(Carbon::SUNDAY) : Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $end = $start->copy()->endOfWeek(Carbon::SATURDAY);

        $events = $this->getEvents($user, $filters)->filter(function ($evt) use ($start, $end) {
            return $evt['date'] >= $start->format('Y-m-d') && $evt['date'] <= $end->format('Y-m-d');
        });

        $eventsByDate = $events->groupBy('date');
        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'day_name' => $cursor->locale('ar')->translatedFormat('l'),
                'day_number' => $cursor->day,
                'is_today' => $cursor->isToday(),
                'events' => $eventsByDate->get($dateStr, collect()),
            ];
            $cursor->addDay();
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'prev_week' => $start->copy()->subWeek()->format('Y-m-d'),
            'next_week' => $start->copy()->addWeek()->format('Y-m-d'),
            'days' => $days,
        ];
    }

    /**
     * Get Agenda view grouped by date.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<string, Collection>
     */
    public function getAgendaView(User $user, array $filters = []): Collection
    {
        return $this->getEvents($user, $filters)->groupBy('date');
    }

    /**
     * Get today's events for user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTodayEvents(User $user): Collection
    {
        $today = Carbon::now()->format('Y-m-d');

        return $this->getEvents($user)->filter(fn ($evt) => $evt['date'] === $today)->values();
    }

    /**
     * Get upcoming events for user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getUpcomingEvents(User $user, int $days = 7): Collection
    {
        $today = Carbon::now()->format('Y-m-d');
        $until = Carbon::now()->addDays($days)->format('Y-m-d');

        return $this->getEvents($user)
            ->filter(fn ($evt) => $evt['date'] >= $today && $evt['date'] <= $until)
            ->values();
    }

    /**
     * Helper to format raw event array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function formatEvent(array $data): array
    {
        return array_merge([
            'id' => '',
            'db_id' => null,
            'title' => '',
            'description' => null,
            'type' => 'task_due',
            'status' => 'open',
            'owner' => null,
            'date' => '',
            'datetime' => null,
            'end_at' => null,
            'related_project' => null,
            'related_team' => null,
            'priority' => 'normal',
            'color_category' => 'copper',
            'location' => null,
            'is_editable' => false,
            'url' => '#',
        ], $data);
    }
}
