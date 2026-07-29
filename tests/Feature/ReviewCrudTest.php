<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_review_for_project(): void
    {
        $reviewer = User::factory()->create();
        $reviewee = User::factory()->create();
        $project = $this->createProject($reviewer);

        $response = $this->actingAs($reviewer)->post(route('projects.reviews.store', $project), [
            'reviewee_id' => $reviewee->id,
            'rating' => 5,
            'comment' => 'Excellent work',
        ]);

        $response->assertRedirect(route('projects.reviews.index', $project));
        $this->assertDatabaseHas('reviews', [
            'project_id' => $project->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $reviewee->id,
            'rating' => 5,
        ]);
    }

    public function test_invalid_review_is_rejected(): void
    {
        $reviewer = User::factory()->create();
        $reviewee = User::factory()->create();
        $project = $this->createProject($reviewer);

        $response = $this->actingAs($reviewer)->from(route('projects.reviews.create', $project))->post(route('projects.reviews.store', $project), [
            'reviewee_id' => $reviewee->id,
            'rating' => 6,
        ]);

        $response->assertRedirect(route('projects.reviews.create', $project));
        $response->assertSessionHasErrors('rating');
    }

    public function test_reviewer_identity_cannot_be_spoofed(): void
    {
        $reviewer = User::factory()->create();
        $otherUser = User::factory()->create();
        $reviewee = User::factory()->create();
        $project = $this->createProject($reviewer);

        $response = $this->actingAs($reviewer)->post(route('projects.reviews.store', $project), [
            'reviewer_id' => $otherUser->id,
            'reviewee_id' => $reviewee->id,
            'rating' => 4,
        ]);

        $response->assertRedirect(route('projects.reviews.index', $project));
        $this->assertDatabaseHas('reviews', [
            'project_id' => $project->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $reviewee->id,
        ]);
    }

    public function test_duplicate_review_for_same_project_and_users_is_rejected(): void
    {
        $reviewer = User::factory()->create();
        $reviewee = User::factory()->create();
        $project = $this->createProject($reviewer);

        Review::create([
            'project_id' => $project->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $reviewee->id,
            'rating' => 4,
            'status' => 'published',
        ]);

        $response = $this->actingAs($reviewer)->from(route('projects.reviews.create', $project))->post(route('projects.reviews.store', $project), [
            'reviewee_id' => $reviewee->id,
            'rating' => 5,
        ]);

        $response->assertRedirect(route('projects.reviews.create', $project));
        $response->assertSessionHasErrors('reviewee_id');
    }

    public function test_unauthorized_user_cannot_delete_review(): void
    {
        $reviewer = User::factory()->create();
        $otherUser = User::factory()->create();
        $reviewee = User::factory()->create();
        $project = $this->createProject($reviewer);
        $review = Review::create([
            'project_id' => $project->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $reviewee->id,
            'rating' => 4,
            'status' => 'published',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('projects.reviews.destroy', [$project, $review]));

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Review Project',
            'slug' => 'review-project-' . uniqid(),
            'description' => 'Project used for review tests.',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);
    }
}
