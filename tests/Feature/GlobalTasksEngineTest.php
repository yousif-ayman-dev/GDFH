<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GlobalTasksEngineTest extends TestCase
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

    public function test_authenticated_user_can_access_global_tasks_page(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('جميع المهام');
    }

    public function test_unauthenticated_user_cannot_access_global_tasks_page(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_global_tasks_page_filters_by_project_status_and_priority(): void
    {
        $user = $this->createOnboardedUser();
        $projectA = Project::factory()->create(['owner_id' => $user->id]);
        $projectB = Project::factory()->create(['owner_id' => $user->id]);

        $taskA = Task::factory()->create([
            'project_id' => $projectA->id,
            'title' => 'Unique Task Alpha',
            'status' => 'in_progress',
            'priority' => 'urgent',
        ]);

        $taskB = Task::factory()->create([
            'project_id' => $projectB->id,
            'title' => 'Unrelated Task Beta',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'project_id' => $projectA->id,
            'status' => 'in_progress',
            'priority' => 'urgent',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Unique Task Alpha');
        $response->assertDontSee('Unrelated Task Beta');
    }
}
