<?php

namespace App\Http\Requests;

use App\Models\Review;
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
        $project = $this->route('project');
        $reviewerId = auth()->id();
        $review = $this->route('review');

        return [
            'reviewee_id' => ['sometimes', 'required', 'integer', 'exists:users,id', function (string $attribute, mixed $value, \Closure $fail) use ($project, $reviewerId, $review): void {
                if ($project === null || $reviewerId === null) {
                    return;
                }

                $query = Review::query()
                    ->where('project_id', $project->id)
                    ->where('reviewer_id', $reviewerId)
                    ->where('reviewee_id', $value);

                if ($review !== null) {
                    $query->where('id', '!=', $review->id);
                }

                $exists = $query->exists();

                if ($exists) {
                    $fail('A review for this project and reviewer already exists for the selected user.');
                }
            }],
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
