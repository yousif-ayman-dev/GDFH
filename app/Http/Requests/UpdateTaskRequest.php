<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['todo', 'in_progress', 'in_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id', function (string $attribute, mixed $value, \Closure $fail): void {
                $project = $this->route('project');
                $task = $this->route('task');
                $teamId = $this->has('team_id') ? $this->input('team_id') : $task?->team_id;

                if ($value === null || $teamId === null || $project === null) {
                    return;
                }

                $isEligibleMember = $project->teams()
                    ->where('teams.id', $teamId)
                    ->whereHas('members', function ($query) use ($value): void {
                        $query->where('users.id', $value);
                    })
                    ->exists();

                if (! $isEligibleMember) {
                    $fail('The selected assignee must be a member of the chosen team.');
                }
            }],
            'team_id' => ['sometimes', 'nullable', 'integer', 'exists:teams,id', function (string $attribute, mixed $value, \Closure $fail): void {
                $project = $this->route('project');

                if ($value === null || $project === null) {
                    return;
                }

                $attachedToProject = $project->teams()->where('teams.id', $value)->exists();

                if (! $attachedToProject) {
                    $fail('The selected team must be linked to the current project.');
                }
            }],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:tasks,id', function (string $attribute, mixed $value, \Closure $fail) use ($project): void {
                $project = $this->route('project');

                if ($value === null || $project === null) {
                    return;
                }

                $belongsToProject = Task::query()
                    ->where('id', $value)
                    ->where('project_id', $project->id)
                    ->exists();

                if (! $belongsToProject) {
                    $fail('The selected parent task must belong to the same project.');
                }
            }],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'due_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_at'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
            'estimated_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
