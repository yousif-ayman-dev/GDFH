<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],

            'description' => ['sometimes', 'required', 'string'],

            'category' => ['sometimes', 'nullable', 'string', 'max:255'],

            'visibility' => [
                'sometimes',
                'required',
                'in:private,marketplace',
            ],

            'budget_type' => [
                'sometimes',
                'nullable',
                'in:fixed,hourly',
            ],

            'budget_min' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'budget_max' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'gte:budget_min',
            ],

            'currency' => [
                'sometimes',
                'required',
                'string',
                'size:3',
            ],

            'start_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'deadline' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }
}
