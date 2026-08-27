<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseDashboardAnalyticsTest extends TestCase
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

    public function test_dashboard_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم والتحليلات');
        $response->assertSee('مؤشرات');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_kpi_calculations_accuracy(): void
    {
        $user = $this->createOnboardedUser();

        // Create 2 projects (1 active, 1 completed)
        $project1 = Project::factory()->create(['owner_id' => $user->id, 'status' => 'in_progress']);
        $project2 = Project::factory()->create(['owner_id' => $user->id, 'status' => 'completed']);

        // Create tasks
        Task::factory()->create(['project_id' => $project1->id, 'status' => 'todo']);
        Task::factory()->create(['project_id' => $project1->id, 'status' => 'completed']);
        Task::factory()->create([
            'project_id' => $project1->id,
            'status' => 'todo',
            'due_at' => now()->subDay(),
        ]);
        Task::factory()->create([
            'project_id' => $project1->id,
            'status' => 'todo',
            'due_at' => now(),
        ]);

        // Create team
        Team::factory()->create(['owner_id' => $user->id]);

        $dashboardService = app(DashboardService::class);
        $data = $dashboardService->getDashboardData($user);

        $this->assertEquals(2, $data['kpis']['total_projects']);
        $this->assertEquals(1, $data['kpis']['active_projects']);
        $this->assertEquals(1, $data['kpis']['completed_projects']);
        $this->assertEquals(4, $data['kpis']['total_tasks']);
        $this->assertEquals(1, $data['kpis']['completed_tasks']);
        $this->assertEquals(1, $data['kpis']['overdue_tasks']);
        $this->assertEquals(1, $data['kpis']['tasks_due_today']);
        $this->assertEquals(1, $data['kpis']['teams_count']);
    }

    public function test_analytics_accuracy(): void
    {
        $user = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $user->id, 'status' => 'in_progress']);
        Task::factory()->count(3)->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory()->count(1)->create(['project_id' => $project->id, 'status' => 'todo']);

        $dashboardService = app(DashboardService::class);
        $data = $dashboardService->getDashboardData($user);

        $this->assertEquals(75, $data['analytics']['task_completion_rate']);
    }

    public function test_widget_rendering(): void
    {
        $client = $this->createOnboardedUser(['account_type' => 'client']);
        $project = Project::factory()->create(['owner_id' => $client->id, 'title' => 'Widget Render Project Test']);

        $response = $this->actingAs($client)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Widget Render Project Test');
        $response->assertSee('مشاريعي المطروحة');

        $freelancer = $this->createOnboardedUser(['account_type' => 'freelancer']);
        $freelancerResponse = $this->actingAs($freelancer)->get(route('dashboard'));
        $freelancerResponse->assertStatus(200);
        $freelancerResponse->assertSee('معدلات إنجاز المشاريع والمهام');
    }
}
