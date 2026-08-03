<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

class KanbanService
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Get Kanban board columns with grouped tasks and statistics.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getBoardColumns(User $user, array $filters = []): array
    {
        // 1. Get user accessible project IDs
        $userTeamIds = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active');
            })
            ->pluck('id');

        $projectIds = Project::query()
            ->where(function ($q) use ($user, $userTeamIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereIn('team_id', $userTeamIds)
                  ->orWhereHas('memberRecords', function ($mq) use ($user) {
                      $mq->where('user_id', $user->id)->where('status', 'active');
                  });
            })
            ->pluck('id');

        // 2. Query tasks with eager loading & counts
        $tasksQuery = Task::query()
            ->whereIn('project_id', $projectIds)
            ->with(['project', 'assignee', 'creator'])
            ->withCount(['comments', 'attachments']);

        // 3. Apply Filters
        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['project_id'])) {
            $tasksQuery->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['team_id'])) {
            $tasksQuery->where('team_id', $filters['team_id']);
        }

        if (! empty($filters['assigned_to'])) {
            $tasksQuery->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['priority'])) {
            $tasksQuery->where('priority', $filters['priority']);
        }

        if (! empty($filters['overdue'])) {
            $tasksQuery->whereNotIn('status', ['completed', 'done', 'cancelled'])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now());
        }

        $allTasks = $tasksQuery->orderBy('sort_order')->latest('updated_at')->get();

        // 4. Group into 4 Columns
        $columns = [
          'todo' => [
              'key' => 'todo',
              'title' => 'قيد الانتظار (To Do)',
              'color' => 'gray',
              'tasks' => collect(),
          ],
          'in_progress' => [
              'key' => 'in_progress',
              'title' => 'قيد التنفيذ (In Progress)',
              'color' => 'blue',
              'tasks' => collect(),
          ],
          'review' => [
              'key' => 'review',
              'title' => 'قيد المراجعة (Review)',
              'color' => 'amber',
              'tasks' => collect(),
          ],
          'done' => [
              'key' => 'done',
              'title' => 'مكتملة (Done)',
              'color' => 'emerald',
              'tasks' => collect(),
          ],
        ];

        foreach ($allTasks as $task) {
            $st = strtolower($task->status);
            if (in_array($st, ['todo', 'pending'], true)) {
                $columns['todo']['tasks']->push($task);
            } elseif ($st === 'in_progress') {
                $columns['in_progress']['tasks']->push($task);
            } elseif (in_array($st, ['review', 'in_review'], true)) {
                $columns['review']['tasks']->push($task);
            } elseif (in_array($st, ['done', 'completed'], true)) {
                $columns['done']['tasks']->push($task);
            } else {
                $columns['todo']['tasks']->push($task);
            }
        }

        // Add task counts
        foreach ($columns as $key => $col) {
            $columns[$key]['count'] = $col['tasks']->count();
        }

        return [
            'columns' => $columns,
            'total_count' => $allTasks->count(),
            'user_projects' => Project::query()->whereIn('id', $projectIds)->get(['id', 'title']),
            'user_teams' => Team::query()->whereIn('id', $userTeamIds)->get(['id', 'name']),
        ];
    }

    /**
     * Update task status through Kanban board.
     */
    public function updateTaskStatus(User $user, Task $task, string $newStatus): Task
    {
        // Normalize column status
        $statusMap = [
            'todo' => 'todo',
            'in_progress' => 'in_progress',
            'review' => 'in_review',
            'done' => 'completed',
        ];

        $targetStatus = $statusMap[$newStatus] ?? $newStatus;

        return $this->taskService->updateStatus($task, $targetStatus);
    }
}
