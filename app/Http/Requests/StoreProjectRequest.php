<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'string', 'in:private,marketplace,public'],
            'status' => ['nullable', 'string', 'in:draft,open,in_progress,on_hold,completed,cancelled'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'budget_type' => ['nullable', 'string', 'in:fixed,hourly'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'currency' => ['nullable', 'string', 'size:3'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'تاريخ بداية المشروع لا يمكن أن يكون قبل اليوم.',
            'due_date.after_or_equal' => 'تاريخ تسليم المشروع لا يمكن أن يكون قبل تاريخ البداية أو اليوم.',
            'deadline.after_or_equal' => 'الموعد النهائي للمشروع لا يمكن أن يكون قبل تاريخ البداية أو اليوم.',
        ];
    }
}
