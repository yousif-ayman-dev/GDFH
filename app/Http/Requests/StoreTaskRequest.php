<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['todo', 'in_progress', 'in_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'start_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'completed_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
