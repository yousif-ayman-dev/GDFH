<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'permanent',
                    'project_based',
                ]),
            ],

            'visibility' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'private',
                    'public',
                ]),
            ],

            'logo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:2048',
            ],
        ];
    }
}
