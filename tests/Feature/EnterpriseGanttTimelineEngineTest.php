<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\GanttService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseGanttTimelineEngineTest extends TestCase
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

    public function test_gantt_page_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('gantt.index'));

        $response->assertStatus(200);
        $response->assertSee('مخطط غانت المتقدم');
        $response->assertSee('شهر');
        $response->assertSee('أسبوع');
        $response->assertSee('يوم');
    }

    public function test_unauthenticated_user_cannot_access_gantt(): void
    {
        $response = $this->get(route('gantt.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_project_and_task_timeline_loading(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'title' => 'Gantt Test Project Alpha',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'deadline' => now()->addDays(20)->format('Y-m-d'),
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Gantt Test Task Beta',
            'start_at' => now()->format('Y-m-d H:i:s'),
            'due_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $ganttService = app(GanttService::class);
        $data = $ganttService->getGanttData($user);

        $this->assertEquals(1, count($data['projects']));
        $this->assertEquals('Gantt Test Project Alpha', $data['projects'][0]['title']);
        $this->assertEquals(1, count($data['projects'][0]['tasks']));
        $this->assertEquals('Gantt Test Task Beta', $data['projects'][0]['tasks'][0]['title']);
    }

    public function test_timeline_and_duration_calculations(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'start_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'deadline' => '2026-08-10',
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'start_at' => '2026-08-02 00:00:00',
            'due_at' => '2026-08-07 00:00:00',
        ]);

        $ganttService = app(GanttService::class);
        $data = $ganttService->getGanttData($user);

        $projData = $data['projects'][0];
        $taskData = $projData['tasks'][0];

        $this->assertEquals(10, $projData['duration']);
        $this->assertEquals(5, $taskData['duration']);
    }

    public function test_overdue_detection_on_gantt(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'status' => 'in_progress',
            'due_date' => now()->subDays(3)->format('Y-m-d'),
            'deadline' => now()->subDays(3)->format('Y-m-d'),
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'todo',
            'due_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $ganttService = app(GanttService::class);
        $data = $ganttService->getGanttData($user);

        $this->assertTrue($data['projects'][0]['overdue']);
        $this->assertTrue($data['projects'][0]['tasks'][0]['overdue']);
    }

    public function test_gantt_filters_and_search(): void
    {
        $user = $this->createOnboardedUser();
        $project1 = Project::factory()->create(['owner_id' => $user->id, 'title' => 'Searchable Project Alpha']);
        $project2 = Project::factory()->create(['owner_id' => $user->id, 'title' => 'Other Project Beta']);

        $ganttService = app(GanttService::class);

        $searchResults = $ganttService->getGanttData($user, ['search' => 'Alpha']);
        $filteredResults = $ganttService->getGanttData($user, ['project_id' => $project2->id]);

        $this->assertEquals(1, count($searchResults['projects']));
        $this->assertEquals('Searchable Project Alpha', $searchResults['projects'][0]['title']);

        $this->assertEquals(1, count($filteredResults['projects']));
        $this->assertEquals('Other Project Beta', $filteredResults['projects'][0]['title']);
    }

    public function test_gantt_workspace_authorization_isolation(): void
    {
        $user1 = $this->createOnboardedUser();
        $user2 = $this->createOnboardedUser();

        Project::factory()->create(['owner_id' => $user1->id, 'title' => 'User 1 Private Timeline']);

        $ganttService = app(GanttService::class);
        $user2Data = $ganttService->getGanttData($user2);

        $this->assertEquals(0, count($user2Data['projects']));
    }
}
