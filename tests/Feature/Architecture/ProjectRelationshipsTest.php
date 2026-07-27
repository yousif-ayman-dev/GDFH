<?php

namespace Tests\Feature\Architecture;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_own_a_project(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'owner_id' => $user->id,
            'title' => 'Marketplace Platform',
            'slug' => 'marketplace-platform',
            'description' => 'Test project',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);

        $this->assertTrue(
            $user->ownedProjects->contains($project)
        );

        $this->assertTrue(
            $project->owner->is($user)
        );
    }

    public function test_user_can_be_a_project_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Project With Member',
            'slug' => 'project-with-member',
            'description' => 'Testing project membership',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);

        $membership = ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->assertTrue(
            $member->projectMemberships->contains($membership)
        );

        $this->assertTrue(
            $membership->project->is($project)
        );

        $this->assertTrue(
            $membership->user->is($member)
        );

        $this->assertTrue(
            $project->members->contains($member)
        );
    }

    public function test_project_can_have_tasks(): void
    {
        $owner = User::factory()->create();

        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Task Project',
            'slug' => 'task-project',
            'description' => 'Testing tasks',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);

        $task = Task::create([
    'project_id' => $project->id,
    'created_by' => $owner->id,
    'assigned_to' => $owner->id,
    'title' => 'Build Backend',
    'description' => 'Build project backend',
    'status' => 'todo',
    'priority' => 'high',
]);

        $this->assertTrue(
            $project->tasks->contains($task)
        );

        $this->assertTrue(
            $task->project->is($project)
        );

        $this->assertTrue(
            $task->creator->is($owner)
        );

        $this->assertTrue(
            $task->assignedUser->is($owner)
        );
    }
}
