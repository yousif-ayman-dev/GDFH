<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\KanbanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseKanbanBoardEngineTest extends TestCase
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

    public function test_kanban_board_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('kanban.index'));

        $response->assertStatus(200);
        $response->assertSee('لوحة المهام والتنفيذ');
        $response->assertSee('قيد الانتظار');
        $response->assertSee('قيد التنفيذ');
        $response->assertSee('مكتملة');
    }

    public function test_unauthenticated_user_cannot_access_kanban(): void
    {
        $response = $this->get(route('kanban.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_task_grouping_into_kanban_columns(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $taskTodo = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);
        $taskProgress = Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);
        $taskReview = Task::factory()->create(['project_id' => $project->id, 'status' => 'review']);
        $taskDone = Task::factory()->create(['project_id' => $project->id, 'status' => 'completed']);

        $kanbanService = app(KanbanService::class);
        $data = $kanbanService->getBoardColumns($user);

        $this->assertEquals(4, $data['total_count']);
        $this->assertTrue($data['columns']['todo']['tasks']->contains('id', $taskTodo->id));
        $this->assertTrue($data['columns']['in_progress']['tasks']->contains('id', $taskProgress->id));
        $this->assertTrue($data['columns']['review']['tasks']->contains('id', $taskReview->id));
        $this->assertTrue($data['columns']['done']['tasks']->contains('id', $taskDone->id));
    }

    public function test_task_status_update_via_kanban(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);

        $response = $this->actingAs($user)->post(route('kanban.tasks.update-status', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    public function test_kanban_filtering_by_project_and_priority(): void
    {
        $user = $this->createOnboardedUser();
        $projectA = Project::factory()->create(['owner_id' => $user->id]);
        $projectB = Project::factory()->create(['owner_id' => $user->id]);

        $taskA = Task::factory()->create(['project_id' => $projectA->id, 'priority' => 'urgent']);
        $taskB = Task::factory()->create(['project_id' => $projectB->id, 'priority' => 'low']);

        $kanbanService = app(KanbanService::class);

        $filteredData = $kanbanService->getBoardColumns($user, [
            'project_id' => $projectA->id,
            'priority' => 'urgent',
        ]);

        $this->assertEquals(1, $filteredData['total_count']);
        $this->assertTrue($filteredData['columns']['todo']['tasks']->contains('id', $taskA->id));
        $this->assertFalse($filteredData['columns']['todo']['tasks']->contains('id', $taskB->id));
    }

    public function test_kanban_search_by_title(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $taskAlpha = Task::factory()->create(['project_id' => $project->id, 'title' => 'Unique Alpha Search Term']);
        $taskBeta = Task::factory()->create(['project_id' => $project->id, 'title' => 'Unrelated Beta Title']);

        $kanbanService = app(KanbanService::class);
        $searchData = $kanbanService->getBoardColumns($user, ['search' => 'Unique Alpha']);

        $this->assertEquals(1, $searchData['total_count']);
        $this->assertTrue($searchData['columns']['todo']['tasks']->contains('id', $taskAlpha->id));
    }

    public function test_unauthorized_user_cannot_update_task_status_on_kanban(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);

        $response = $this->actingAs($stranger)->post(route('kanban.tasks.update-status', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(403);
    }
}
