<?php

namespace App\Http\Requests;

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
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['todo', 'in_progress', 'in_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:tasks,id'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'due_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_at'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
            'estimated_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
