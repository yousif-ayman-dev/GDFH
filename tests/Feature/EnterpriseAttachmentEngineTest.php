<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseAttachmentEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_user_can_upload_attachment_to_project(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $file = UploadedFile::fake()->create('specification.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attachments', [
            'original_name' => 'specification.pdf',
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_download_attachment(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $file = UploadedFile::fake()->create('contract.pdf', 300, 'application/pdf');

        $this->actingAs($user)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $attachment = Attachment::where('original_name', 'contract.pdf')->firstOrFail();

        $response = $this->actingAs($user)->get(route('attachments.download', $attachment));

        $response->assertStatus(200);
    }

    public function test_user_can_replace_attachment(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $initialFile = UploadedFile::fake()->create('v1.pdf', 200, 'application/pdf');
        $this->actingAs($user)->post(route('projects.attachments.store', $project), ['file' => $initialFile]);

        $attachment = Attachment::where('original_name', 'v1.pdf')->firstOrFail();

        $newFile = UploadedFile::fake()->createWithContent('v2_updated.pdf', '%PDF-1.4 Updated content distinct hash');
        $response = $this->actingAs($user)->post(route('attachments.replace', $attachment), [
            'file' => $newFile,
        ]);

        $response->assertRedirect();
        $this->assertEquals('v2_updated.pdf', $attachment->fresh()->original_name);
    }

    public function test_uploader_or_project_owner_can_delete_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('draft.docx', 150, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), ['file' => $file]);

        $attachment = Attachment::where('original_name', 'draft.docx')->firstOrFail();

        $response = $this->actingAs($owner)->delete(route('attachments.destroy', $attachment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    public function test_unauthorized_user_cannot_delete_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('confidential.pdf', 200, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), ['file' => $file]);

        $attachment = Attachment::where('original_name', 'confidential.pdf')->firstOrFail();

        $response = $this->actingAs($stranger)->delete(route('attachments.destroy', $attachment));

        $response->assertStatus(403);
    }

    public function test_invalid_file_extension_is_rejected(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $executableFile = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($user)->post(route('projects.attachments.store', $project), [
            'file' => $executableFile,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('attachments', ['original_name' => 'virus.exe']);
    }

    public function test_attachment_actions_log_activity(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $file = UploadedFile::fake()->create('blueprint.png', 400, 'image/png');

        $this->actingAs($user)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('activities', [
            'event' => 'attachment_uploaded',
            'user_id' => $user->id,
            'subject_id' => $project->id,
        ]);
    }
}
