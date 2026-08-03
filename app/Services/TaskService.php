<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Map of allowed task workflow transitions.
     */
    protected array $allowedTransitions = [
        'todo' => ['in_progress', 'cancelled'],
        'in_progress' => ['review', 'in_review', 'todo', 'cancelled'],
        'review' => ['completed', 'done', 'in_progress', 'cancelled'],
        'in_review' => ['completed', 'done', 'in_progress', 'cancelled'],
        'completed' => ['review', 'in_progress'],
        'done' => ['review', 'in_progress'],
        'cancelled' => ['todo'],
    ];

    /**
     * Create a new task within a project.
     */
    public function createTask(User $creator, Project $project, array $data): Task
    {
        if (isset($data['assigned_to']) && $data['assigned_to']) {
            $this->validateAssignment($project, (int) $data['assigned_to']);
        }

        return DB::transaction(function () use ($creator, $project, $data) {
            $status = $data['status'] ?? 'todo';
            $dueAt = $data['due_at'] ?? $data['due_date'] ?? null;

            $task = Task::create([
                'project_id' => $project->id,
                'team_id' => $data['team_id'] ?? $project->team_id,
                'created_by' => $creator->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $status,
                'priority' => $data['priority'] ?? 'medium',
                'start_at' => $data['start_at'] ?? now(),
                'due_at' => $dueAt,
                'completed_at' => in_array($status, ['completed', 'done'], true) ? now() : null,
                'estimated_minutes' => $data['estimated_minutes'] ?? null,
            ]);

            $this->activityService->logTaskCreated($creator, $task);

            if ($task->assigned_to && $assignee = User::find($task->assigned_to)) {
                $this->activityService->logTaskAssigned($creator, $task, $assignee);
            }

            if (in_array($status, ['completed', 'done'], true)) {
                $this->activityService->logTaskCompleted($creator, $task);
            }

            return $task;
        });
    }

    /**
     * Update task parameters.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $project = $task->project;

        if (isset($data['assigned_to']) && $data['assigned_to'] && (int) $data['assigned_to'] !== (int) $task->assigned_to) {
            $this->validateAssignment($project, (int) $data['assigned_to']);
        }

        if (isset($data['status']) && $data['status'] !== $task->status) {
            $this->validateWorkflowTransition($task->status, $data['status']);
        }

        return DB::transaction(function () use ($task, $data) {
            if (isset($data['due_date']) && ! isset($data['due_at'])) {
                $data['due_at'] = $data['due_date'];
            }

            if (isset($data['status'])) {
                if (in_array($data['status'], ['completed', 'done'], true) && ! $task->completed_at) {
                    $data['completed_at'] = now();
                } elseif (! in_array($data['status'], ['completed', 'done'], true)) {
                    $data['completed_at'] = null;
                }
            }

            $oldAssignedTo = $task->assigned_to;
            $task->update($data);

            if (isset($data['assigned_to']) && $data['assigned_to'] && (int)$data['assigned_to'] !== (int)$oldAssignedTo) {
                if ($assignee = User::find($data['assigned_to'])) {
                    $this->activityService->logTaskAssigned(Auth::user(), $task, $assignee);
                }
            }

            if (isset($data['status']) && in_array($data['status'], ['completed', 'done'], true)) {
                $this->activityService->logTaskCompleted(Auth::user(), $task);
            }

            return $task->fresh();
        });
    }

    /**
     * Change task status.
     */
    public function changeStatus(Task $task, string $targetStatus): Task
    {
        if ($task->status === $targetStatus) {
            return $task;
        }

        $this->validateWorkflowTransition($task->status, $targetStatus);

        return DB::transaction(function () use ($task, $targetStatus) {
            $payload = ['status' => $targetStatus];

            if (in_array($targetStatus, ['completed', 'done'], true)) {
                $payload['completed_at'] = now();
            } else {
                $payload['completed_at'] = null;
            }

            $task->update($payload);

            if (in_array($targetStatus, ['completed', 'done'], true)) {
                $this->activityService->logTaskCompleted(Auth::user(), $task);
            }

            return $task->fresh();
        });
    }

    /**
     * Alias for changeStatus.
     */
    public function updateStatus(Task $task, string $targetStatus): Task
    {
        return $this->changeStatus($task, $targetStatus);
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->delete();
        });
    }

    /**
     * Verify that the assigned user is a member of the project or its team.
     */
    public function validateAssignment(Project $project, int $userId): void
    {
        if ($project->owner_id === $userId) {
            return;
        }

        $isProjectMember = $project->members()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if ($isProjectMember) {
            return;
        }

        if ($project->team_id && $project->team) {
            $isTeamMember = $project->team->memberships()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->exists();

            if ($isTeamMember) {
                return;
            }
        }

        $isLinkedTeamMember = $project->teams()
            ->whereHas('memberships', fn ($q) => $q->where('user_id', $userId)->where('status', 'active'))
            ->exists();

        if ($isLinkedTeamMember) {
            return;
        }

        throw ValidationException::withMessages([
            'assigned_to' => 'لا يمكن تعيين مهمة لمستخدم خارج فريق أو أعضاء المشروع.',
        ]);
    }

    /**
     * Validate workflow status transition.
     */
    public function validateWorkflowTransition(string $currentStatus, string $targetStatus): void
    {
        $allowed = $this->allowedTransitions[$currentStatus] ?? [];

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "الانتقال من حالة '{$currentStatus}' إلى '{$targetStatus}' غير مسموح به.",
            ]);
        }
    }
}
