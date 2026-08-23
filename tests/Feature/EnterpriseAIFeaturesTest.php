<?php

namespace Tests\Feature;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\AI\AIProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EnterpriseAIFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username'     => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attrs));
    }

    protected function mockAIProvider(string $returnValue = 'استجابة الذكاء الاصطناعي التجريبية'): void
    {
        $mock = Mockery::mock(AIProviderInterface::class);
        $mock->shouldReceive('generateResponse')->andReturn($returnValue);
        $mock->shouldReceive('analyzeWorkspace')->andReturn([
            'health_score' => 85,
            'recommendations' => [],
            'warnings' => [],
            'strengths' => [],
            'weaknesses' => [],
            'total_projects' => 0,
            'total_tasks' => 0,
            'overdue_tasks' => 0,
        ]);
        $this->app->instance(AIProviderInterface::class, $mock);
    }

    // ─── Requirement #15: Analyze Project Description ─────────────────────────

    public function test_analyze_project_endpoint_requires_auth(): void
    {
        $response = $this->postJson(route('ai.analyze-project'), [
            'description' => 'بناء منصة تجارة إلكترونية متكاملة بـ Laravel',
        ]);
        $response->assertStatus(401);
    }

    public function test_analyze_project_returns_ai_suggestions(): void
    {
        $this->mockAIProvider('Laravel، MySQL، Vue.js: مهارات موصى بها لهذا المشروع');
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->postJson(route('ai.analyze-project'), [
            'description' => 'بناء منصة تجارة إلكترونية متكاملة بـ Laravel وقاعدة بيانات MySQL وواجهة أمامية Vue.js',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'suggestions']);
        $response->assertJson(['success' => true]);
    }

    public function test_analyze_project_validates_description_min_length(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->postJson(route('ai.analyze-project'), [
            'description' => 'قصير', // less than 20 chars
        ]);

        $response->assertStatus(422);
    }

    public function test_analyze_project_validates_description_required(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->postJson(route('ai.analyze-project'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_analyze_project_requires_onboarding(): void
    {
        $user = User::factory()->create([
            'account_type'  => 'client',
            'onboarded_at'  => null,
            'username'      => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('ai.analyze-project'), [
            'description' => 'مشروع تجريبي بوصف طويل يمتد لأكثر من عشرين حرفاً',
        ]);

        // Should be redirected to onboarding
        $response->assertStatus(302);
    }

    // ─── Requirement #16: Suggest Team Members ────────────────────────────────

    public function test_suggest_members_requires_auth(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->postJson(route('ai.suggest-members'), [
            'team_id' => $team->id,
        ]);
        $response->assertStatus(401);
    }

    public function test_suggest_members_returns_suggestions_to_team_owner(): void
    {
        $this->mockAIProvider('اقتراح 1: محمد — مطور Laravel ممتاز');

        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        // Create freelancer candidates
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);
        FreelancerProfile::create([
            'user_id'                  => $freelancer->id,
            'title'                    => 'مطور لاراڤيل',
            'skills'                   => ['Laravel', 'PHP'],
            'hourly_rate'              => 40,
            'rating'                   => 4.8,
            'reviews_count'            => 5,
            'completed_projects_count' => 8,
            'availability'             => 'available',
        ]);

        $response = $this->actingAs($owner)->postJson(route('ai.suggest-members'), [
            'team_id' => $team->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'suggestions' => ['text', 'freelancers'],
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_suggest_members_returns_forbidden_for_non_member(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $outsider = $this->createOnboardedUser();

        $response = $this->actingAs($outsider)->postJson(route('ai.suggest-members'), [
            'team_id' => $team->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_suggest_members_validates_team_id(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->postJson(route('ai.suggest-members'), [
            'team_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team_id']);
    }

    public function test_suggest_members_allowed_for_active_team_member(): void
    {
        $this->mockAIProvider('اقتراح عضو جديد من الذكاء الاصطناعي');

        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $member = $this->createOnboardedUser();

        TeamMember::create([
            'team_id'   => $team->id,
            'user_id'   => $member->id,
            'role'      => 'member',
            'status'    => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($member)->postJson(route('ai.suggest-members'), [
            'team_id' => $team->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // ─── Requirement #17: Recommended Projects ────────────────────────────────

    public function test_recommended_projects_requires_auth(): void
    {
        $response = $this->getJson(route('ai.recommended-projects'));
        $response->assertStatus(401);
    }

    public function test_recommended_projects_returns_empty_for_non_freelancer(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);

        $response = $this->actingAs($client)->getJson(route('ai.recommended-projects'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'projects' => []]);
    }

    public function test_recommended_projects_returns_matching_projects_for_freelancer(): void
    {
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);
        FreelancerProfile::create([
            'user_id'                  => $freelancer->id,
            'title'                    => 'مطور Laravel',
            'skills'                   => ['Laravel', 'PHP'],
            'hourly_rate'              => 40,
            'rating'                   => 4.5,
            'reviews_count'            => 3,
            'completed_projects_count' => 5,
            'availability'             => 'available',
        ]);

        $owner = $this->createOnboardedUser();
        Project::factory()->create([
            'owner_id'    => $owner->id,
            'title'       => 'مشروع Laravel متكامل',
            'description' => 'نحتاج مطور PHP Laravel لبناء API',
            'visibility'  => 'public',
            'status'      => 'in_progress',
        ]);

        $response = $this->actingAs($freelancer)->getJson(route('ai.recommended-projects'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'projects']);
    }

    public function test_recommended_projects_excludes_own_projects(): void
    {
        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);
        FreelancerProfile::create([
            'user_id'                  => $freelancer->id,
            'title'                    => 'مطور',
            'skills'                   => ['Laravel'],
            'hourly_rate'              => 30,
            'rating'                   => 4.0,
            'reviews_count'            => 2,
            'completed_projects_count' => 3,
            'availability'             => 'available',
        ]);

        // Project owned by the freelancer themselves should NOT appear
        Project::factory()->create([
            'owner_id'    => $freelancer->id,
            'title'       => 'مشروع Laravel خاص بالمستقل',
            'visibility'  => 'public',
            'status'      => 'in_progress',
        ]);

        $response = $this->actingAs($freelancer)->getJson(route('ai.recommended-projects'));
        $data = $response->json();

        $projectIds = collect($data['projects'])->pluck('id')->toArray();
        $this->assertNotContains(
            Project::where('owner_id', $freelancer->id)->first()->id,
            $projectIds
        );
    }

    public function test_recommended_projects_requires_onboarding(): void
    {
        $user = User::factory()->create([
            'account_type'  => 'freelancer',
            'onboarded_at'  => null,
            'username'      => null,
        ]);

        $response = $this->actingAs($user)->getJson(route('ai.recommended-projects'));
        $response->assertStatus(302);
    }
}
