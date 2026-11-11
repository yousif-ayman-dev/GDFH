<?php

namespace Tests\Feature\Security;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateFileAccessSecurityTest extends TestCase
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

    public function test_direct_public_access_to_attachments_directory_is_forbidden(): void
    {
        $response = $this->get('/storage/attachments/confidential_contract.pdf');

        $response->assertStatus(403);
    }

    public function test_directory_traversal_is_blocked(): void
    {
        $response = $this->get('/storage/..%2F..%2F.env');

        $response->assertStatus(403);
    }

    public function test_disallowed_storage_paths_are_forbidden(): void
    {
        $response = $this->get('/storage/arbitrary_file.txt');

        $response->assertStatus(403);
    }

    public function test_legitimate_public_media_is_accessible_when_file_exists(): void
    {
        // Place a public avatar in the public storage
        Storage::disk('public')->put('avatars/sample_avatar.png', 'fake_image_content');

        $response = $this->get('/storage/avatars/sample_avatar.png');

        $response->assertStatus(200);
    }

    public function test_legitimate_public_media_returns_404_when_file_does_not_exist(): void
    {
        $response = $this->get('/storage/avatars/missing_avatar.png');

        $response->assertStatus(404);
    }

    public function test_authorized_project_owner_can_download_private_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('project_scope.pdf', 300, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $attachment = Attachment::where('original_name', 'project_scope.pdf')->firstOrFail();

        $response = $this->actingAs($owner)->get(route('attachments.download', $attachment));

        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_download_private_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $unauthorizedUser = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('project_scope.pdf', 300, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $attachment = Attachment::where('original_name', 'project_scope.pdf')->firstOrFail();

        $response = $this->actingAs($unauthorizedUser)->get(route('attachments.download', $attachment));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_when_attempting_to_download_attachment(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $file = UploadedFile::fake()->create('project_scope.pdf', 300, 'application/pdf');
        $this->actingAs($owner)->post(route('projects.attachments.store', $project), [
            'file' => $file,
        ]);

        $attachment = Attachment::where('original_name', 'project_scope.pdf')->firstOrFail();

        auth()->logout();

        $response = $this->get(route('attachments.download', $attachment));

        $response->assertRedirect(route('login'));
    }
}
