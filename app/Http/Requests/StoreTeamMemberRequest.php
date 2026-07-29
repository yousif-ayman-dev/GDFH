<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'role' => [
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
