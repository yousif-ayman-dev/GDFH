<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    /**
     * Create a new enterprise project.
     */
    public function createProject(User $owner, array $data): Project
    {
        return DB::transaction(function () use ($owner, $data) {
            $project = Project::create([
                'owner_id' => $owner->id,
                'team_id' => $data['team_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? '',
                'category' => $data['category'] ?? null,
                'visibility' => $data['visibility'] ?? 'private',
                'status' => $data['status'] ?? 'draft',
                'budget' => $data['budget'] ?? null,
                'budget_type' => $data['budget_type'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? $data['deadline'] ?? null,
                'deadline' => $data['deadline'] ?? $data['due_date'] ?? null,
                'published_at' => ($data['status'] ?? 'draft') === 'open' ? now() : null,
            ]);

            return $project;
        });
    }

    /**
     * Update an existing project's parameters.
     */
    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            if (isset($data['due_date']) && ! isset($data['deadline'])) {
                $data['deadline'] = $data['due_date'];
            }

            $project->update($data);

            return $project->fresh();
        });
    }

    /**
     * Archive a project.
     */
    public function archiveProject(Project $project): void
    {
        if ($project->isArchived()) {
            return;
        }

        $project->update([
            'archived_at' => now(),
        ]);
    }

    /**
     * Restore an archived project.
     */
    public function restoreProject(Project $project): void
    {
        if (! $project->isArchived()) {
            return;
        }

        $project->update([
            'archived_at' => null,
        ]);
    }

    /**
     * Change project status.
     */
    public function changeStatus(Project $project, string $status): void
    {
        $allowedStatuses = ['draft', 'open', 'in_progress', 'on_hold', 'completed', 'cancelled'];

        if (! in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'حالة المشروع المحددة غير صالحة.',
            ]);
        }

        $payload = ['status' => $status];

        if ($status === 'open' && ! $project->published_at) {
            $payload['published_at'] = now();
        }

        if ($status === 'completed') {
            $payload['completed_at'] = now();
        }

        $project->update($payload);
    }
}
