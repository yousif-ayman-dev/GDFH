<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'permanent',
                    'project_based',
                ]),
            ],

            'visibility' => [
                'required',
                'string',
                Rule::in([
                    'private',
                    'public',
                ]),
            ],
        ];
    }
}
