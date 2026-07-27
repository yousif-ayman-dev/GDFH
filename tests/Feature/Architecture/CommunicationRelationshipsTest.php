<?php

namespace Tests\Feature\Architecture;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Communication Test Project',
            'slug' => 'communication-test-project',
            'description' => 'Project used for communication tests',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);
    }

    private function createTask(Project $project, User $user): Task
    {
        return Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Communication Test Task',
            'description' => 'Task used for communication tests',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
    }

    public function test_user_can_comment_on_a_task(): void
    {
        $user = User::factory()->create();

        $project = $this->createProject($user);
        $task = $this->createTask($project, $user);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => 'This is a test comment.',
        ]);

        $this->assertTrue(
            $task->comments->contains($comment)
        );

        $this->assertTrue(
            $comment->task->is($task)
        );

        $this->assertTrue(
            $comment->user->is($user)
        );

        $this->assertTrue(
            $user->comments->contains($comment)
        );
    }

    public function test_attachment_can_belong_to_a_task(): void
    {
        $user = User::factory()->create();

        $project = $this->createProject($user);
        $task = $this->createTask($project, $user);

        $attachment = Attachment::create([
            'uploaded_by' => $user->id,
            'attachable_type' => Task::class,
            'attachable_id' => $task->id,
            'original_name' => 'requirements.pdf',
            'stored_name' => 'requirements-test.pdf',
            'disk' => 'local',
            'path' => 'attachments/requirements-test.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 1024,
            'visibility' => 'private',
        ]);

        $this->assertTrue(
            $task->attachments->contains($attachment)
        );

        $this->assertTrue(
            $attachment->attachable->is($task)
        );

        $this->assertTrue(
            $attachment->uploadedBy->is($user)
        );

        $this->assertTrue(
            $user->attachments->contains($attachment)
        );
    }

    public function test_attachment_can_belong_to_a_project(): void
    {
        $user = User::factory()->create();

        $project = $this->createProject($user);

        $attachment = Attachment::create([
            'uploaded_by' => $user->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'project-brief.pdf',
            'stored_name' => 'project-brief-test.pdf',
            'disk' => 'local',
            'path' => 'attachments/project-brief-test.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 2048,
            'visibility' => 'private',
        ]);

        $this->assertTrue(
            $project->attachments->contains($attachment)
        );

        $this->assertTrue(
            $attachment->attachable->is($project)
        );

        $this->assertTrue(
            $attachment->uploadedBy->is($user)
        );
    }
}
