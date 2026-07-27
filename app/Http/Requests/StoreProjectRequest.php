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
            'title' => ['required', 'string', 'max:255'],

            'description' => ['required', 'string'],

            'category' => ['nullable', 'string', 'max:255'],

            'visibility' => [
                'required',
                'in:private,marketplace',
            ],

            'budget_type' => [
                'nullable',
                'in:fixed,hourly',
            ],

            'budget_min' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'budget_max' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:budget_min',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'deadline' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }
}
