<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard');

        $response->assertViewHasAll([
            'stats',
            'activeProjects',
            'upcomingTasks',
            'projectDeadlines',
            'teams',
        ]);
    }

    public function test_dashboard_counts_only_active_accessible_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createProject($user, [
            'title' => 'Owned Open Project',
            'status' => 'open',
        ]);

        $this->createProject($user, [
            'title' => 'Owned In Progress Project',
            'status' => 'in_progress',
        ]);

        $this->createProject($user, [
            'title' => 'Completed Project',
            'status' => 'completed',
        ]);

        $this->createProject($user, [
            'title' => 'Cancelled Project',
            'status' => 'cancelled',
        ]);

        $memberProject = $this->createProject($otherUser, [
            'title' => 'Member Project',
            'status' => 'open',
        ]);

        $memberProject->members()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->createProject($otherUser, [
            'title' => 'Unrelated Project',
            'status' => 'open',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['active_projects'] === 3;
        });
    }

    public function test_dashboard_counts_open_tasks_assigned_to_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $project = $this->createProject($user, [
            'status' => 'in_progress',
        ]);

        $this->createTask($project, $user, [
            'title' => 'Todo Task',
            'status' => 'todo',
        ]);

        $this->createTask($project, $user, [
            'title' => 'In Progress Task',
            'status' => 'in_progress',
        ]);

        $this->createTask($project, $user, [
            'title' => 'In Review Task',
            'status' => 'in_review',
        ]);

        $this->createTask($project, $user, [
            'title' => 'Completed Task',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Cancelled Task',
            'status' => 'cancelled',
        ]);

        $this->createTask($project, $otherUser, [
            'title' => 'Other User Task',
            'status' => 'todo',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['open_tasks'] === 3;
        });
    }

    public function test_dashboard_counts_only_overdue_open_tasks_assigned_to_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $project = $this->createProject($user, [
            'status' => 'in_progress',
        ]);

        $this->createTask($project, $user, [
            'title' => 'Overdue Task',
            'status' => 'todo',
            'due_at' => now()->subDay(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Future Task',
            'status' => 'todo',
            'due_at' => now()->addDay(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Completed Overdue Task',
            'status' => 'completed',
            'due_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Cancelled Overdue Task',
            'status' => 'cancelled',
            'due_at' => now()->subDays(3),
        ]);

        $this->createTask($project, $otherUser, [
            'title' => 'Other User Overdue Task',
            'status' => 'todo',
            'due_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['overdue_tasks'] === 1;
        });
    }

    public function test_dashboard_counts_owned_and_active_member_teams(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Team::query()->create([
            'owner_id' => $user->id,
            'name' => 'Owned Team',
            'description' => 'Team owned by the current user.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $activeMemberTeam = Team::query()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Active Member Team',
            'description' => 'Team where the current user is active.',
            'type' => 'project_based',
            'visibility' => 'private',
        ]);

        $activeMemberTeam->members()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $pendingMemberTeam = Team::query()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Pending Member Team',
            'description' => 'Pending membership should not count.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $pendingMemberTeam->members()->attach($user->id, [
            'role' => 'member',
            'status' => 'pending',
        ]);

        Team::query()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Unrelated Team',
            'description' => 'Team unrelated to the current user.',
            'type' => 'project_based',
            'visibility' => 'public',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['teams'] === 2;
        });
    }

    public function test_dashboard_calculates_project_progress_from_completed_tasks(): void
    {
        $user = User::factory()->create();

        $project = $this->createProject($user, [
            'title' => 'Progress Project',
            'status' => 'in_progress',
        ]);

        $this->createTask($project, $user, [
            'title' => 'Completed Task One',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Completed Task Two',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->createTask($project, $user, [
            'title' => 'Open Task One',
            'status' => 'todo',
        ]);

        $this->createTask($project, $user, [
            'title' => 'Open Task Two',
            'status' => 'in_progress',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('activeProjects', function ($projects) use ($project): bool {
            $dashboardProject = $projects->firstWhere('id', $project->id);

            return $dashboardProject !== null
                && $dashboardProject->tasks_count === 4
                && $dashboardProject->completed_tasks_count === 2
                && $dashboardProject->progress_percentage === 50;
        });
    }

    public function test_dashboard_does_not_expose_unrelated_user_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $visibleProject = $this->createProject($user, [
            'title' => 'Visible Project',
            'status' => 'open',
        ]);

        $hiddenProject = $this->createProject($otherUser, [
            'title' => 'Hidden Project',
            'status' => 'open',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('activeProjects', function ($projects) use (
            $visibleProject,
            $hiddenProject
        ): bool {
            return $projects->contains('id', $visibleProject->id)
                && ! $projects->contains('id', $hiddenProject->id);
        });
    }

    public function test_dashboard_returns_upcoming_tasks_and_project_deadlines(): void
    {
        $user = User::factory()->create();

        $project = $this->createProject($user, [
            'title' => 'Deadline Project',
            'status' => 'in_progress',
            'deadline' => today()->addDays(5),
        ]);

        $task = $this->createTask($project, $user, [
            'title' => 'Upcoming Task',
            'status' => 'todo',
            'due_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('upcomingTasks', function ($tasks) use ($task): bool {
            return $tasks->contains('id', $task->id);
        });

        $response->assertViewHas('projectDeadlines', function ($projects) use ($project): bool {
            return $projects->contains('id', $project->id);
        });
    }

    private function createProject(User $owner, array $attributes = []): Project
    {
        return Project::query()->create(array_merge([
            'owner_id' => $owner->id,
            'title' => 'Test Project',
            'slug' => 'project-' . fake()->unique()->slug(),
            'description' => 'Dashboard test project.',
            'category' => 'development',
            'visibility' => 'private',
            'status' => 'draft',
            'budget_type' => null,
            'budget_min' => null,
            'budget_max' => null,
            'currency' => 'USD',
            'start_date' => null,
            'deadline' => null,
            'published_at' => null,
            'completed_at' => null,
        ], $attributes));
    }

    private function createTask(
        Project $project,
        User $assignedUser,
        array $attributes = []
    ): Task {
        return Task::query()->create(array_merge([
            'project_id' => $project->id,
            'created_by' => $project->owner_id,
            'assigned_to' => $assignedUser->id,
            'parent_id' => null,
            'title' => 'Test Task',
            'description' => null,
            'status' => 'todo',
            'priority' => 'medium',
            'start_at' => null,
            'due_at' => null,
            'completed_at' => null,
            'estimated_minutes' => null,
            'sort_order' => 0,
        ], $attributes));
    }
}
