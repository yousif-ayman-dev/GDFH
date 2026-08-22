<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseReportsAnalyticsTest extends TestCase
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

    public function test_reports_page_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('التقارير وتحليلات الإنتاجية');
        $response->assertSee('مؤشر الإنتاجية');
        $response->assertSee('معدل اكتمال المهام');
    }

    public function test_unauthenticated_user_cannot_access_reports(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_report_analytics_calculations_and_productivity_score(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'todo',
        ]);

        Worklog::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'duration' => 7200, // 2 hours
        ]);

        $reportService = app(ReportService::class);
        $data = $reportService->generateReport($user);

        $kpis = $data['kpis'];

        $this->assertEquals(50, $kpis['completion_rate']);
        $this->assertEquals(1, $kpis['completed_tasks']);
        $this->assertEquals(2, $kpis['total_tasks']);
        $this->assertEquals(2.0, $kpis['total_tracked_hours']);
        $this->assertGreaterThan(0, $kpis['productivity_score']);
    }

    public function test_chart_datasets_structure(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);

        $reportService = app(ReportService::class);
        $data = $reportService->generateReport($user);

        $charts = $data['charts'];

        $this->assertArrayHasKey('line_chart', $charts);
        $this->assertArrayHasKey('bar_chart', $charts);
        $this->assertArrayHasKey('pie_chart', $charts);
        $this->assertArrayHasKey('area_chart', $charts);

        $this->assertCount(4, $charts['line_chart']['labels']);
        $this->assertCount(4, $charts['pie_chart']['labels']);
    }

    public function test_report_filtering_by_project_status_and_priority(): void
    {
        $user = $this->createOnboardedUser();
        $projectA = Project::factory()->create(['owner_id' => $user->id, 'title' => 'Project A']);
        $projectB = Project::factory()->create(['owner_id' => $user->id, 'title' => 'Project B']);

        Task::factory()->create(['project_id' => $projectA->id, 'status' => 'completed', 'priority' => 'high']);
        Task::factory()->create(['project_id' => $projectB->id, 'status' => 'todo', 'priority' => 'low']);

        $reportService = app(ReportService::class);

        $filteredData = $reportService->generateReport($user, [
            'project_id' => $projectA->id,
            'status' => 'completed',
            'priority' => 'high',
        ]);

        $this->assertEquals(1, $filteredData['kpis']['total_tasks']);
        $this->assertEquals(100, $filteredData['kpis']['completion_rate']);
    }

    public function test_user_leaderboard_generation(): void
    {
        $user = $this->createOnboardedUser();
        $worker = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $worker->id,
            'status' => 'completed',
        ]);

        $reportService = app(ReportService::class);
        $data = $reportService->generateReport($user);

        $leaderboard = $data['reports']['user_leaderboard'];

        $this->assertCount(1, $leaderboard);
        $this->assertEquals($worker->id, $leaderboard[0]['user']->id);
        $this->assertEquals(1, $leaderboard[0]['completed_tasks']);
    }

    public function test_reports_workspace_isolation(): void
    {
        $user1 = $this->createOnboardedUser();
        $user2 = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $user1->id]);
        Task::factory()->create(['project_id' => $project->id]);

        $reportService = app(ReportService::class);
        $user2Data = $reportService->generateReport($user2);

        $this->assertEquals(0, $user2Data['kpis']['total_projects']);
        $this->assertEquals(0, $user2Data['kpis']['total_tasks']);
    }

    public function test_authenticated_user_can_export_reports_as_csv(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id, 'title' => 'مشروع التصدير التجريبي']);
        Task::factory()->create(['project_id' => $project->id, 'title' => 'مهمة التصدير']);

        $response = $this->actingAs($user)->get(route('reports.export.csv', [
            'project_id' => $project->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('تقرير تحليلات الإنتاجية', $content);
        $this->assertStringContainsString('مشروع التصدير التجريبي', $content);
    }

    public function test_authenticated_user_can_export_reports_as_pdf(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id, 'title' => 'مشروع الطباعة']);

        $response = $this->actingAs($user)->get(route('reports.export.pdf', [
            'project_id' => $project->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('تقرير تحليلات الإنتاجية والأداء');
        $response->assertSee('مشروع الطباعة');
    }

    public function test_reports_export_does_not_leak_unauthorized_project_data(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $secretProject = Project::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'مشروع سري للغاية',
        ]);

        $response = $this->actingAs($stranger)->get(route('reports.export.csv', [
            'project_id' => $secretProject->id,
        ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringNotContainsString('مشروع سري للغاية', $content);
    }
}
