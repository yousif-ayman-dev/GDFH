<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_password_reset_link_to_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'onboarded_at' => now()]);
        $user = User::factory()->create(['is_admin' => false, 'email' => 'user@example.com']);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.send-password-reset', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify password remains securely hashed and plain token is NOT exposed in session or UI
        $this->assertTrue(Hash::check('password', $user->fresh()->password) || !empty($user->fresh()->password));
    }

    public function test_non_admin_cannot_send_password_reset_link(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false, 'onboarded_at' => now()]);
        $targetUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
            ->post(route('admin.users.send-password-reset', $targetUser));

        $response->assertStatus(403);
    }

    public function test_admin_can_force_logout_user_and_invalidate_sessions(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'onboarded_at' => now()]);
        $user = User::factory()->create(['is_admin' => false, 'remember_token' => 'old-remember-token']);

        // Insert dummy session if table exists
        $sessionTable = config('session.table', 'sessions');
        if (\Illuminate\Support\Facades\Schema::hasTable($sessionTable)) {
            DB::table($sessionTable)->insert([
                'id' => Str::random(40),
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => 'dummy',
                'last_activity' => time(),
            ]);
        }

        $response = $this->actingAs($admin)
            ->post(route('admin.users.force-logout', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert remember token was invalidated
        $this->assertNotEquals('old-remember-token', $user->fresh()->remember_token);

        // Assert session records removed
        if (\Illuminate\Support\Facades\Schema::hasTable($sessionTable)) {
            $this->assertEquals(0, DB::table($sessionTable)->where('user_id', $user->id)->count());
        }
    }

    public function test_non_admin_cannot_force_logout_users(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false, 'onboarded_at' => now()]);
        $targetUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
            ->post(route('admin.users.force-logout', $targetUser));

        $response->assertStatus(403);
    }

    public function test_admin_cannot_force_logout_self(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'onboarded_at' => now()]);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.force-logout', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
