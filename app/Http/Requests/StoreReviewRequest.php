<?php

namespace App\Http\Requests;

use App\Models\Review;
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
        $project = $this->route('project');
        $reviewerId = auth()->id();

        return [
            'reviewee_id' => ['required', 'integer', 'exists:users,id', function (string $attribute, mixed $value, \Closure $fail) use ($project, $reviewerId): void {
                if ($project === null || $reviewerId === null) {
                    return;
                }

                $exists = Review::query()
                    ->where('project_id', $project->id)
                    ->where('reviewer_id', $reviewerId)
                    ->where('reviewee_id', $value)
                    ->exists();

                if ($exists) {
                    $fail('A review for this project and reviewer already exists for the selected user.');
                }
            }],
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
