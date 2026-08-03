<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewService
{
    public function __construct(
        protected ActivityService $activityService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Submit a rating and review for a completed project.
     */
    public function submitReview(
        User $reviewer,
        Project $project,
        int $rating,
        ?string $comment = null,
        ?int $targetRevieweeId = null
    ): Review {
        $revieweeId = $targetRevieweeId;

        if (! $revieweeId) {
            $contract = Contract::where('project_id', $project->id)->first();

            if ((int) $reviewer->id === (int) $project->owner_id) {
                $revieweeId = $contract?->freelancer_id;

                if (! $revieweeId) {
                    $member = $project->members()->where('user_id', '!=', $reviewer->id)->first();
                    $revieweeId = $member?->user_id;
                }
            } else {
                $revieweeId = $project->owner_id;
            }
        }

        if (! $revieweeId) {
            throw new InvalidArgumentException('لم يتم العثور على شريك لتقييمه.');
        }

        $existing = Review::where('project_id', $project->id)
            ->where('reviewer_id', $reviewer->id)
            ->where('reviewee_id', $revieweeId)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('لقد قمت بإضافة تقييم لهذا الشريك في هذا المشروع سابقاً.');
        }

        return DB::transaction(function () use ($reviewer, $revieweeId, $project, $rating, $comment) {
            $review = Review::create([
                'project_id' => $project->id,
                'reviewer_id' => $reviewer->id,
                'reviewee_id' => $revieweeId,
                'rating' => max(1, min(5, $rating)),
                'comment' => $comment,
                'status' => 'published',
            ]);

            // Update Freelancer Profile ratings if reviewee has profile
            $avgRating = Review::where('reviewee_id', $revieweeId)->avg('rating') ?? 5.0;
            $reviewsCount = Review::where('reviewee_id', $revieweeId)->count();
            $completedCount = Contract::where('freelancer_id', $revieweeId)
                ->where('status', 'completed')
                ->count();

            FreelancerProfile::updateOrCreate(
                ['user_id' => $revieweeId],
                [
                    'rating' => round($avgRating, 2),
                    'reviews_count' => $reviewsCount,
                    'completed_projects_count' => $completedCount,
                ]
            );

            // Activity & Notification
            $reviewee = User::find($revieweeId);

            if ($reviewee) {
                $this->notificationService->sendNotification(
                    $reviewee,
                    'تقييم ورأي جديد',
                    "قام {$reviewer->name} بترك تقييم {$rating}/5 نجوم لمشروع ({$project->title}).",
                    route('projects.show', $project)
                );
            }

            return $review;
        });
    }

    /**
     * Get reviews received by user.
     */
    public function getUserReviews(User $user): Collection
    {
        return Review::query()
            ->where('reviewee_id', $user->id)
            ->where('status', 'published')
            ->with(['reviewer', 'project'])
            ->latest()
            ->get();
    }

    /**
     * Get reviews submitted for a project.
     */
    public function getProjectReviews(Project $project): Collection
    {
        return Review::query()
            ->where('project_id', $project->id)
            ->where('status', 'published')
            ->with(['reviewer', 'reviewee'])
            ->latest()
            ->get();
    }
}
