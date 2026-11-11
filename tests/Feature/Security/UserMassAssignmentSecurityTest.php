<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMassAssignmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_fillable_excludes_sensitive_privileges_and_balances(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertNotContains('is_admin', $fillable);
        $this->assertNotContains('is_banned', $fillable);
        $this->assertNotContains('is_verified', $fillable);
        $this->assertNotContains('verification_badge_at', $fillable);
        $this->assertNotContains('connects_balance', $fillable);
    }

    public function test_mass_assignment_update_does_not_modify_admin_privileges(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $user->update([
            'is_admin' => true,
        ]);

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_mass_assignment_update_does_not_modify_ban_status(): void
    {
        $user = User::factory()->create([
            'is_banned' => false,
        ]);

        $user->update([
            'is_banned' => true,
        ]);

        $this->assertFalse($user->fresh()->is_banned);
    }

    public function test_mass_assignment_update_does_not_modify_verification_or_connects(): void
    {
        $user = User::factory()->create([
            'is_verified' => false,
            'connects_balance' => 10,
        ]);

        $user->update([
            'is_verified' => true,
            'verification_badge_at' => now(),
            'connects_balance' => 9999,
        ]);

        $fresh = $user->fresh();
        $this->assertFalse((bool) $fresh->is_verified);
        $this->assertNull($fresh->verification_badge_at);
        $this->assertEquals(10, $fresh->connects_balance);
    }

    public function test_profile_update_request_ignores_injected_sensitive_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'legit@tasker.com',
            'is_admin' => false,
            'is_banned' => false,
            'is_verified' => false,
            'connects_balance' => 20,
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'legit@tasker.com',
            'is_admin' => 1,
            'is_banned' => 1,
            'is_verified' => 1,
            'connects_balance' => 999,
        ]);

        $response->assertSessionHasNoErrors();
        $fresh = $user->fresh();

        $this->assertEquals('Updated Name', $fresh->name);
        $this->assertFalse($fresh->is_admin);
        $this->assertFalse($fresh->is_banned);
        $this->assertFalse((bool) $fresh->is_verified);
        $this->assertEquals(20, $fresh->connects_balance);
    }

    public function test_admin_controller_can_still_toggle_admin_and_ban_via_authorized_methods(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'onboarded_at' => now(),
        ]);

        $targetUser = User::factory()->create([
            'is_admin' => false,
            'is_banned' => false,
            'onboarded_at' => now(),
        ]);

        $targetToBan = User::factory()->create([
            'is_admin' => false,
            'is_banned' => false,
            'onboarded_at' => now(),
        ]);

        // Toggle admin status
        $this->actingAs($admin)->post(route('admin.users.toggle-admin', $targetUser));
        $this->assertTrue($targetUser->fresh()->is_admin);

        // Toggle ban status on non-admin user
        $this->actingAs($admin)->post(route('admin.users.toggle-ban', $targetToBan));
        $this->assertTrue($targetToBan->fresh()->is_banned);
    }
}
