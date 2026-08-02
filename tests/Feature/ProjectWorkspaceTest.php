<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectWorkspaceTest extends TestCase
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

    public function test_workspace_page_renders_successfully_for_owner(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'team_id' => $team->id,
            'title' => 'E-Commerce Platform Rebuild',
            'status' => 'in_progress',
        ]);

        Task::factory()->count(2)->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('بيئة عمل المشروع');
        $response->assertSee('E-Commerce Platform Rebuild');
        $response->assertSee('قيد التنفيذ');
        $response->assertSee('إضافة مهمة جديدة');
        $response->assertSee('إدارة حالة المشروع');
    }

    public function test_workspace_displays_tasks_and_members(): void
    {
        $owner = $this->createOnboardedUser();
        $member = $this->createOnboardedUser(['username' => 'lead_dev']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'team_id' => $team->id,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
            'title' => 'Design Database Schema',
        ]);

        $response = $this->actingAs($owner)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('Design Database Schema');
        $response->assertSee('lead_dev');
    }

    public function test_unauthorized_user_cannot_view_private_workspace(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($stranger)->get(route('projects.show', $project));

        $response->assertStatus(403);
    }
}
