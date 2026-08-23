<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseSettingsProfileTest extends TestCase
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

    public function test_settings_page_is_displayed_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('إعدادات النظام والتفضيلات');
        $response->assertSee('الملف الشخصي والصورة');
        $response->assertSee('تفضيلات الإشعارات');
        $response->assertSee('مظهر النظام');
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_update_bio_and_profile_info(): void
    {
        $user = $this->createOnboardedUser(['name' => 'Original Name']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'bio' => 'مطور برمجيات خبير في Laravel و PHP.',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertEquals('Updated User Name', $user->name);
        $this->assertEquals('مطور برمجيات خبير في Laravel و PHP.', $user->bio);
    }

    public function test_user_can_upload_avatar_image(): void
    {
        Storage::fake('public');

        $user = $this->createOnboardedUser();
        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_invalid_avatar_upload_fails_validation(): void
    {
        Storage::fake('public');

        $user = $this->createOnboardedUser();
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors(['avatar']);
        $user->refresh();
        $this->assertNull($user->avatar_path);
    }

    public function test_user_can_delete_own_avatar(): void
    {
        Storage::fake('public');

        $user = $this->createOnboardedUser();
        $file = UploadedFile::fake()->create('avatar.png', 100, 'image/png');

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $user->refresh();
        $avatarPath = $user->avatar_path;
        Storage::disk('public')->assertExists($avatarPath);

        $response = $this->actingAs($user)->delete(route('profile.avatar.destroy'));

        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->avatar_path);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_user_can_update_notification_preferences(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->patch(route('settings.notifications.update'), [
            'preferences' => [
                'email' => '1',
                'in_app' => '0',
                'task_assigned' => '1',
                'team_invite' => '0',
            ],
        ]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertTrue($user->getNotificationPreference('email'));
        $this->assertFalse($user->getNotificationPreference('in_app'));
        $this->assertTrue($user->getNotificationPreference('task_assigned'));
        $this->assertFalse($user->getNotificationPreference('team_invite'));
    }
}
