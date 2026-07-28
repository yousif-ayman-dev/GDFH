<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_member_to_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($owner)
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'role' => 'member',
            ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'invited_by' => $owner->id,
        ]);

        $projectMember = ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $member->id)
            ->first();

        $this->assertNotNull($projectMember);
        $this->assertNotNull($projectMember->joined_at);
    }

    public function test_owner_cannot_be_added_as_project_member(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($owner)
            ->from(route('projects.show', $project))
            ->post(route('projects.members.store', $project), [
                'user_id' => $owner->id,
                'role' => 'member',
            ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_same_user_cannot_be_added_twice(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = $this->createProject($owner);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'invited_by' => $owner->id,
            'joined_at' => now(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->from(route('projects.show', $project))
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'role' => 'viewer',
            ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('user_id');

        $this->assertSame(
            1,
            ProjectMember::query()
                ->where('project_id', $project->id)
                ->where('user_id', $member->id)
                ->count()
        );
    }

    public function test_non_owner_cannot_add_member(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $member = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($otherUser)
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'role' => 'member',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_invalid_role_cannot_be_used_when_adding_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($owner)
            ->from(route('projects.show', $project))
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'role' => 'owner',
            ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_update_project_member(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $project = $this->createProject($owner);
        $member = $this->createMember($project, $user, $owner);

        $response = $this
            ->actingAs($owner)
            ->patch(
                route('projects.members.update', [$project, $member]),
                [
                    'role' => 'team_leader',
                    'status' => 'suspended',
                ]
            );

        $response->assertRedirect(route('projects.show', $project));

        $member->refresh();

        $this->assertSame('team_leader', $member->role);
        $this->assertSame('suspended', $member->status);
        $this->assertNull($member->left_at);
    }

    public function test_setting_member_status_to_left_sets_left_at(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $project = $this->createProject($owner);
        $member = $this->createMember($project, $user, $owner);

        $response = $this
            ->actingAs($owner)
            ->patch(
                route('projects.members.update', [$project, $member]),
                [
                    'status' => 'left',
                ]
            );

        $response->assertRedirect(route('projects.show', $project));

        $member->refresh();

        $this->assertSame('left', $member->status);
        $this->assertNotNull($member->left_at);
    }

    public function test_non_owner_cannot_update_project_member(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $user = User::factory()->create();
        $project = $this->createProject($owner);
        $member = $this->createMember($project, $user, $owner);

        $response = $this
            ->actingAs($otherUser)
            ->patch(
                route('projects.members.update', [$project, $member]),
                [
                    'role' => 'project_manager',
                ]
            );

        $response->assertForbidden();

        $member->refresh();

        $this->assertSame('member', $member->role);
    }

    public function test_member_from_another_project_cannot_be_updated(): void
    {
        $owner = User::factory()->create();
        $secondOwner = User::factory()->create();
        $user = User::factory()->create();

        $project = $this->createProject($owner);
        $otherProject = $this->createProject($secondOwner);

        $member = $this->createMember(
            $otherProject,
            $user,
            $secondOwner
        );

        $response = $this
            ->actingAs($owner)
            ->patch(
                route('projects.members.update', [$project, $member]),
                [
                    'role' => 'team_leader',
                ]
            );

        $response->assertNotFound();

        $member->refresh();

        $this->assertSame('member', $member->role);
    }

    public function test_owner_can_remove_project_member(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $project = $this->createProject($owner);
        $member = $this->createMember($project, $user, $owner);

        $response = $this
            ->actingAs($owner)
            ->delete(
                route('projects.members.destroy', [$project, $member])
            );

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_members', [
            'id' => $member->id,
        ]);
    }

    public function test_non_owner_cannot_remove_project_member(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $user = User::factory()->create();
        $project = $this->createProject($owner);
        $member = $this->createMember($project, $user, $owner);

        $response = $this
            ->actingAs($otherUser)
            ->delete(
                route('projects.members.destroy', [$project, $member])
            );

        $response->assertForbidden();

        $this->assertDatabaseHas('project_members', [
            'id' => $member->id,
        ]);
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Test Project',
            'slug' => 'test-project-' . uniqid(),
            'description' => 'Project used for member testing.',
            'category' => 'Development',
            'visibility' => 'private',
            'status' => 'draft',
            'budget_type' => 'fixed',
            'budget_min' => 100,
            'budget_max' => 500,
            'currency' => 'USD',
            'start_date' => '2026-08-01',
            'deadline' => '2026-09-01',
        ]);
    }

    private function createMember(
        Project $project,
        User $user,
        User $inviter
    ): ProjectMember {
        return ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => 'member',
            'status' => 'active',
            'invited_by' => $inviter->id,
            'joined_at' => now(),
        ]);
    }
}
