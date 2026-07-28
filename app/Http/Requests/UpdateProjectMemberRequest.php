<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'project_manager',
                    'team_leader',
                    'member',
                    'viewer',
                ]),
            ],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'pending',
                    'active',
                    'suspended',
                    'left',
                ]),
            ],
        ];
    }
}
