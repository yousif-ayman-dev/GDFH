<?php

namespace Tests\Feature\Architecture;

use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_review_another_user_for_a_project(): void
    {
        $owner = User::factory()->create();
        $freelancer = User::factory()->create();

        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Review Test Project',
            'slug' => 'review-test-project',
            'description' => 'Project used for review tests',
            'visibility' => 'private',
            'status' => 'completed',
            'currency' => 'USD',
        ]);

        $review = Review::create([
            'project_id' => $project->id,
            'reviewer_id' => $owner->id,
            'reviewee_id' => $freelancer->id,
            'rating' => 5,
            'communication_rating' => 5,
            'quality_rating' => 5,
            'professionalism_rating' => 5,
            'deadline_rating' => 4,
            'comment' => 'Excellent work.',
            'status' => 'published',
        ]);

        $this->assertTrue(
            $review->project->is($project)
        );

        $this->assertTrue(
            $review->reviewer->is($owner)
        );

        $this->assertTrue(
            $review->reviewee->is($freelancer)
        );

        $this->assertTrue(
            $project->reviews->contains($review)
        );

        $this->assertTrue(
            $owner->reviewsWritten->contains($review)
        );

        $this->assertTrue(
            $freelancer->reviewsReceived->contains($review)
        );
    }

    public function test_detailed_ratings_are_cast_to_integers(): void
    {
        $owner = User::factory()->create();
        $freelancer = User::factory()->create();

        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Rating Cast Test',
            'slug' => 'rating-cast-test',
            'description' => 'Testing rating casts',
            'visibility' => 'private',
            'status' => 'completed',
            'currency' => 'USD',
        ]);

        $review = Review::create([
            'project_id' => $project->id,
            'reviewer_id' => $owner->id,
            'reviewee_id' => $freelancer->id,
            'rating' => 4,
            'communication_rating' => 5,
            'quality_rating' => 4,
            'professionalism_rating' => 5,
            'deadline_rating' => 3,
            'status' => 'published',
        ]);

        $this->assertIsInt($review->rating);
        $this->assertIsInt($review->communication_rating);
        $this->assertIsInt($review->quality_rating);
        $this->assertIsInt($review->professionalism_rating);
        $this->assertIsInt($review->deadline_rating);
    }
}
