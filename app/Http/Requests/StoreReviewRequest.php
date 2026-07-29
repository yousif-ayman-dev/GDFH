<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewee_id' => ['required', 'integer', 'exists:users,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'communication_rating' => ['nullable', 'integer', 'between:1,5'],
            'quality_rating' => ['nullable', 'integer', 'between:1,5'],
            'professionalism_rating' => ['nullable', 'integer', 'between:1,5'],
            'deadline_rating' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'published', 'hidden'])],
        ];
    }
}
