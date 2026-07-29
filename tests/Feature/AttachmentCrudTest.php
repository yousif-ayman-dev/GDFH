<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_attachment_for_project(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $file = UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf');

        $response = $this->actingAs($owner)->post(route('projects.attachments.store', $project), [
            'file' => $file,
            'visibility' => 'private',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('attachments', [
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'uploaded_by' => $owner->id,
        ]);
    }

    public function test_invalid_attachment_is_rejected(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $file = UploadedFile::fake()->create('notes.txt', 50, 'text/plain');

        $response = $this->actingAs($owner)->from(route('projects.show', $project))->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('file');
    }

    public function test_unauthorized_user_cannot_add_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = $this->createProject($owner);
        $file = UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf');

        $response = $this->actingAs($otherUser)->post(route('projects.attachments.store', $project), [
            'file' => $file,
            'visibility' => 'private',
        ]);

        $response->assertForbidden();
    }

    public function test_attachment_can_be_deleted(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $attachment = $this->createAttachment($project, $owner);

        $response = $this->actingAs($owner)->delete(route('projects.attachments.destroy', [$project, $attachment]));

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Attachment Project',
            'slug' => 'attachment-project-' . uniqid(),
            'description' => 'Project used for attachment tests.',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);
    }

    private function createAttachment(Project $project, User $user): Attachment
    {
        return Attachment::create([
            'uploaded_by' => $user->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'brief.pdf',
            'stored_name' => 'brief.pdf',
            'disk' => 'local',
            'path' => 'attachments/brief.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'visibility' => 'private',
        ]);
    }
}
