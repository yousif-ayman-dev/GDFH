<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewee_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'rating' => ['sometimes', 'required', 'integer', 'between:1,5'],
            'communication_rating' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'quality_rating' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'professionalism_rating' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'deadline_rating' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'comment' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'published', 'hidden'])],
        ];
    }
}
