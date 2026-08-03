<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseReviewsRatingsTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_client_can_submit_review_on_completed_project(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'completed']);

        Contract::factory()->create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($client)->post(route('projects.reviews.store', $project), [
            'rating' => 5,
            'comment' => 'Outstanding work! Delivered ahead of deadline.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'project_id' => $project->id,
            'reviewer_id' => $client->id,
            'reviewee_id' => $freelancer->id,
            'rating' => 5,
        ]);
    }

    public function test_cannot_submit_review_on_non_completed_project(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'open']);

        $response = $this->actingAs($client)->post(route('projects.reviews.store', $project), [
            'rating' => 5,
            'comment' => 'Test comment',
        ]);

        $response->assertSessionHasErrors('review');
    }

    public function test_duplicate_review_submission_is_prevented(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'completed']);

        Review::factory()->create([
            'project_id' => $project->id,
            'reviewer_id' => $client->id,
            'reviewee_id' => $freelancer->id,
        ]);

        $response = $this->actingAs($client)->post(route('projects.reviews.store', $project), [
            'rating' => 4,
            'comment' => 'Duplicate review attempt.',
        ]);

        $response->assertSessionHasErrors('review');
    }

    public function test_submitting_review_updates_freelancer_profile_average_rating(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);

        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'completed']);
        Contract::factory()->create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'completed',
        ]);

        $service = app(ReviewService::class);
        $service->submitReview($client, $project, 4, 'Great job');

        $profile = FreelancerProfile::where('user_id', $freelancer->id)->first();

        $this->assertNotNull($profile);
        $this->assertEquals(4.00, $profile->rating);
        $this->assertEquals(1, $profile->reviews_count);
    }

    public function test_invalid_rating_value_is_rejected(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $project = Project::factory()->create(['owner_id' => $client->id, 'status' => 'completed']);

        $response = $this->actingAs($client)->post(route('projects.reviews.store', $project), [
            'rating' => 10,
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $project = Project::factory()->create(['status' => 'completed']);

        $response = $this->post(route('projects.reviews.store', $project), [
            'rating' => 5,
        ]);

        $response->assertRedirect(route('login'));
    }
}
