<?php

namespace Tests\Feature\Security;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateStorageHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
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

    public function test_new_attachment_is_stored_on_private_disk_by_default(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('confidential_specs.pdf', 250, 'application/pdf');

        $response = $this->actingAs($owner)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $response->assertRedirect();

        $attachment = Attachment::where('original_name', 'confidential_specs.pdf')->firstOrFail();

        // Database asserts
        $this->assertEquals('private', $attachment->disk);
        $this->assertEquals(64, strlen($attachment->checksum)); // SHA-256

        // Physical storage asserts: MUST exist on private disk, MUST NOT exist on public disk
        $this->assertTrue(Storage::disk('private')->exists($attachment->path));
        $this->assertFalse(Storage::disk('public')->exists($attachment->path));
    }

    public function test_authorized_user_can_download_private_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('project_deliverable.pdf', 300, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), ['file' => $file]);

        $attachment = Attachment::where('original_name', 'project_deliverable.pdf')->firstOrFail();

        $response = $this->actingAs($owner)->get(route('attachments.download', $attachment));

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-disposition') !== null);
    }

    public function test_unauthorized_user_is_forbidden_from_downloading_private_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('trade_secrets.pdf', 100, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), ['file' => $file]);

        $attachment = Attachment::where('original_name', 'trade_secrets.pdf')->firstOrFail();

        $response = $this->actingAs($stranger)->get(route('attachments.download', $attachment));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_on_download_attempt(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), ['file' => $file]);

        $attachment = Attachment::where('original_name', 'contract.pdf')->firstOrFail();

        auth()->logout();

        $response = $this->get(route('attachments.download', $attachment));

        $response->assertRedirect(route('login'));
    }

    public function test_legacy_public_disk_attachment_remains_accessible_through_authorized_controller(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        // Create legacy file directly on public disk
        $legacyContent = '%PDF-1.4 Legacy contract content';
        $legacyPath = 'attachments/legacy_contract.pdf';
        Storage::disk('public')->put($legacyPath, $legacyContent);

        // Record in DB with disk = 'public'
        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'legacy_contract.pdf',
            'stored_name' => 'legacy_contract.pdf',
            'disk' => 'public',
            'path' => $legacyPath,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => strlen($legacyContent),
            'checksum' => hash('sha256', $legacyContent),
            'visibility' => 'private',
        ]);

        // Authorized download through controller should resolve strictly from recorded public disk
        $response = $this->actingAs($owner)->get(route('attachments.download', $attachment));
        $response->assertStatus(200);

        // Unauthorized user still gets 403
        $stranger = $this->createOnboardedUser();
        $forbiddenResponse = $this->actingAs($stranger)->get(route('attachments.download', $attachment));
        $forbiddenResponse->assertStatus(403);
    }

    public function test_missing_attachment_file_returns_404_without_silent_disk_fallback(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        // Record exists pointing to private disk, but file physically exists ONLY on public disk
        $phantomPath = 'attachments/phantom.pdf';
        Storage::disk('public')->put($phantomPath, 'content on public');
        // Do NOT put on private disk

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'phantom.pdf',
            'stored_name' => 'phantom.pdf',
            'disk' => 'private',
            'path' => $phantomPath,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 15,
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($owner)->get(route('attachments.download', $attachment));

        // Strict disk resolution must abort 404 and NOT silently read from public disk
        $response->assertStatus(404);
    }

    public function test_polymorphic_comment_attachment_authorization(): void
    {
        $owner = $this->createOnboardedUser();
        $commenter = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($commenter->id, [
            'status' => 'active',
            'role' => 'member',
        ]);

        $comment = Comment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Here is the diagram.',
        ]);

        $filePath = 'attachments/diagram.png';
        Storage::disk('private')->put($filePath, 'fake image data');

        $attachment = Attachment::create([
            'user_id' => $commenter->id,
            'uploaded_by' => $commenter->id,
            'attachable_type' => Comment::class,
            'attachable_id' => $comment->id,
            'original_name' => 'diagram.png',
            'stored_name' => 'diagram.png',
            'disk' => 'private',
            'path' => $filePath,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 15,
            'visibility' => 'private',
        ]);

        // Project owner can download
        $this->actingAs($owner)->get(route('attachments.download', $attachment))->assertStatus(200);

        // Legitimate commenter and project member can download
        $this->actingAs($commenter)->get(route('attachments.download', $attachment))->assertStatus(200);

        // Stranger cannot download
        $this->actingAs($stranger)->get(route('attachments.download', $attachment))->assertStatus(403);
    }

    public function test_uploader_without_access_to_private_resource_cannot_download_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $unauthorizedUploader = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $comment = Comment::create([
            'project_id' => $project->id,
            'user_id' => $unauthorizedUploader->id,
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Unauthorized comment attachment attempt.',
        ]);

        $filePath = 'attachments/private_doc.pdf';
        Storage::disk('private')->put($filePath, 'secret data');

        $attachment = Attachment::create([
            'user_id' => $unauthorizedUploader->id,
            'uploaded_by' => $unauthorizedUploader->id,
            'attachable_type' => Comment::class,
            'attachable_id' => $comment->id,
            'original_name' => 'private_doc.pdf',
            'stored_name' => 'private_doc.pdf',
            'disk' => 'private',
            'path' => $filePath,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 11,
            'visibility' => 'private',
        ]);

        // Even though user is the uploader, being the uploader does not bypass private resource authorization
        $response = $this->actingAs($unauthorizedUploader)->get(route('attachments.download', $attachment));
        $response->assertStatus(403);
    }

    public function test_uploader_without_access_to_private_resource_cannot_replace_or_delete_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $unauthorizedUploader = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $filePath = 'attachments/private_spec.pdf';
        Storage::disk('private')->put($filePath, 'original content');

        $attachment = Attachment::create([
            'user_id' => $unauthorizedUploader->id,
            'uploaded_by' => $unauthorizedUploader->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'private_spec.pdf',
            'stored_name' => 'private_spec.pdf',
            'disk' => 'private',
            'path' => $filePath,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 16,
            'visibility' => 'private',
        ]);

        // Unauthorized uploader cannot replace
        $newFile = UploadedFile::fake()->createWithContent('malicious.pdf', '%PDF-1.4 replaced content');
        $replaceResponse = $this->actingAs($unauthorizedUploader)->post(route('attachments.replace', $attachment), [
            'file' => $newFile,
        ]);
        $replaceResponse->assertStatus(403);

        // Unauthorized uploader cannot delete
        $deleteResponse = $this->actingAs($unauthorizedUploader)->delete(route('attachments.destroy', $attachment));
        $deleteResponse->assertStatus(403);
    }

    public function test_migration_command_dry_run_does_not_modify_disk_or_database(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $content = 'Sample content for dry-run';
        $path = 'attachments/dry_run_doc.pdf';
        Storage::disk('public')->put($path, $content);

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'dry_run_doc.pdf',
            'stored_name' => 'dry_run_doc.pdf',
            'disk' => 'public',
            'path' => $path,
            'size' => strlen($content),
            'checksum' => hash('sha256', $content),
            'visibility' => 'private',
        ]);

        $exitCode = Artisan::call('attachments:migrate-to-private', ['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('public', $attachment->fresh()->disk);
        $this->assertFalse(Storage::disk('private')->exists($path));
    }

    public function test_migration_command_copies_file_verifies_checksum_and_updates_database(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $content = 'Real content to migrate';
        $path = 'attachments/real_doc.pdf';
        Storage::disk('public')->put($path, $content);

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'real_doc.pdf',
            'stored_name' => 'real_doc.pdf',
            'disk' => 'public',
            'path' => $path,
            'size' => strlen($content),
            'checksum' => hash('sha256', $content),
            'visibility' => 'private',
        ]);

        $exitCode = Artisan::call('attachments:migrate-to-private');

        $this->assertEquals(0, $exitCode);

        // Record updated to private disk
        $fresh = $attachment->fresh();
        $this->assertEquals('private', $fresh->disk);

        // File exists on target private disk
        $this->assertTrue(Storage::disk('private')->exists($fresh->path));

        // Source file is PRESERVED on public disk (non-destructive)
        $this->assertTrue(Storage::disk('public')->exists($path));

        // SHA-256 match
        $this->assertEquals(hash('sha256', $content), hash('sha256', Storage::disk('private')->get($fresh->path)));
    }

    public function test_migration_command_is_resumable_and_skips_already_migrated_records(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        // Attachment already on private disk
        Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'already_migrated.pdf',
            'stored_name' => 'already_migrated.pdf',
            'disk' => 'private',
            'path' => 'attachments/already_migrated.pdf',
            'visibility' => 'private',
        ]);

        $exitCode = Artisan::call('attachments:migrate-to-private');
        $this->assertEquals(0, $exitCode);

        $output = Artisan::output();
        $this->assertStringContainsString('No legacy attachments on [public] disk found', $output);
    }

    public function test_migration_command_detects_and_skips_missing_source_files(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        // DB record exists pointing to public disk, but file physically missing from public
        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'uploaded_by' => $owner->id,
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => 'lost_source.pdf',
            'stored_name' => 'lost_source.pdf',
            'disk' => 'public',
            'path' => 'attachments/lost_source.pdf',
            'visibility' => 'private',
        ]);

        $exitCode = Artisan::call('attachments:migrate-to-private');
        $this->assertEquals(0, $exitCode);

        // Record must remain untouched on public disk
        $this->assertEquals('public', $attachment->fresh()->disk);

        $output = Artisan::output();
        $this->assertStringContainsString('Source file missing from public disk', $output);
    }
}
