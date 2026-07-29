<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
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
                    'owner',
                    'manager',
                    'member',
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
                ]),
            ],
        ];
    }
}
