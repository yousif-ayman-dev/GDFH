<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectWorkflowService
{
    /**
     * Map of allowed status transitions.
     */
    protected array $allowedTransitions = [
        'draft' => ['open', 'cancelled'],
        'open' => ['in_progress', 'on_hold', 'cancelled'],
        'in_progress' => ['review', 'on_hold', 'cancelled'],
        'review' => ['completed', 'in_progress', 'on_hold', 'cancelled'],
        'completed' => ['archived'],
        'on_hold' => ['open', 'in_progress', 'review'],
        'cancelled' => ['draft'],
        'archived' => [],
    ];

    /**
     * Change project status adhering strictly to state machine transitions.
     */
    public function changeStatus(Project $project, string $targetStatus): void
    {
        if ($project->status === $targetStatus) {
            return;
        }

        if ($project->isArchived() && $targetStatus !== 'archived') {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تغيير حالة مشروع مؤرشف. يرجى استعادة المشروع أولاً.',
            ]);
        }

        $currentStatus = $project->status;
        $allowed = $this->allowedTransitions[$currentStatus] ?? [];

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "الانتقال من حالة '{$currentStatus}' إلى '{$targetStatus}' غير مسموح به.",
            ]);
        }

        DB::transaction(function () use ($project, $targetStatus) {
            $payload = [
                'status' => $targetStatus,
            ];

            if ($targetStatus === 'open' && ! $project->published_at) {
                $payload['published_at'] = now();
            }

            if ($targetStatus === 'completed') {
                $payload['completed_at'] = now();
            }

            if ($targetStatus === 'archived') {
                $payload['archived_at'] = now();
            }

            $project->update($payload);
        });
    }

    /**
     * Archive project.
     */
    public function archive(Project $project): void
    {
        if ($project->isArchived()) {
            return;
        }

        DB::transaction(function () use ($project) {
            $project->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);
        });
    }

    /**
     * Restore archived project.
     */
    public function restore(Project $project): void
    {
        if (! $project->isArchived()) {
            return;
        }

        DB::transaction(function () use ($project) {
            $newStatus = $project->completed_at !== null ? 'completed' : 'in_progress';

            $project->update([
                'status' => $newStatus,
                'archived_at' => null,
            ]);
        });
    }
}
