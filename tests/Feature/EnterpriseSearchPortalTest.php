<?php

namespace Tests\Feature;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseSearchPortalTest extends TestCase
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

    public function test_authenticated_user_can_access_search_portal(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('search.index'));

        $response->assertStatus(200);
        $response->assertSee('بوابة البحث الشاملة');
    }

    public function test_search_portal_returns_matching_projects(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'title' => 'مشروع الذكاء الاصطناعي الخاص',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'الذكاء']));

        $response->assertStatus(200);
        $response->assertSee('مشروع الذكاء الاصطناعي الخاص');
    }

    public function test_search_portal_returns_matching_tasks(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'title' => 'مهمة تحسين الأداء وتأمين API',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'تحسين الأداء']));

        $response->assertStatus(200);
        $response->assertSee('مهمة تحسين الأداء وتأمين API');
    }

    public function test_search_portal_returns_matching_teams(): void
    {
        $user = $this->createOnboardedUser();
        $team = Team::factory()->create([
            'owner_id' => $user->id,
            'name' => 'فريق التطوير السريع',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'التطوير السريع']));

        $response->assertStatus(200);
        $response->assertSee('فريق التطوير السريع');
    }

    public function test_search_portal_returns_matching_marketplace_services(): void
    {
        $user = $this->createOnboardedUser();
        $service = Service::create([
            'user_id' => $user->id,
            'title' => 'خدمة بناء تطبيقات الصوت والفيديو',
            'slug' => 'audio-video-app-dev',
            'description' => 'تقديم حلول متقدمة لبناء شبكات التواصل والصوت.',
            'price' => 500.00,
            'delivery_days' => 7,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'الصوت والفيديو']));

        $response->assertStatus(200);
        $response->assertSee('خدمة بناء تطبيقات الصوت والفيديو');
    }

    public function test_search_portal_returns_matching_freelancers(): void
    {
        $user = $this->createOnboardedUser();
        $freelancer = $this->createOnboardedUser([
            'name' => 'المهندس خبير الخوارزميات',
            'account_type' => 'freelancer',
        ]);
        FreelancerProfile::create([
            'user_id' => $freelancer->id,
            'title' => 'خبير الذكاء الاصطناعي وتعلم الآلة',
            'hourly_rate' => 75.00,
        ]);

        $response = $this->actingAs($user)->get(route('search.index', ['q' => 'الخوارزميات']));

        $response->assertStatus(200);
        $response->assertSee('المهندس خبير الخوارزميات');
    }

    public function test_search_portal_filters_by_entity_type(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'title' => 'مشروع النظام التجاري',
        ]);

        $response = $this->actingAs($user)->get(route('search.index', [
            'q' => 'التجاري',
            'type' => 'projects',
        ]));

        $response->assertStatus(200);
        $response->assertSee('مشروع النظام التجاري');
    }

    public function test_user_cannot_see_unauthorized_private_projects_or_tasks_in_search(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $privateProject = Project::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'مشروع سري للغاية للمالك',
            'visibility' => 'private',
        ]);

        $privateTask = Task::factory()->create([
            'project_id' => $privateProject->id,
            'created_by' => $owner->id,
            'title' => 'مهمة سرية جداً للمالك',
        ]);

        $response = $this->actingAs($stranger)->get(route('search.index', ['q' => 'سري']));

        $response->assertStatus(200);
        $response->assertDontSee('مشروع سري للغاية للمالك');
        $response->assertDontSee('مهمة سرية جداً للمالك');
    }
}
