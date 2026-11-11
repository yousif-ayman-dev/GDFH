<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBanEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@tasker.com',
            'password' => bcrypt('password'),
            'is_banned' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'banned@tasker.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_session_of_banned_user_is_terminated_on_subsequent_request(): void
    {
        $user = User::factory()->create([
            'is_banned' => true,
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_banned_user_json_request_is_rejected_with_forbidden_status(): void
    {
        $user = User::factory()->create([
            'is_banned' => true,
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('notifications.poll'));

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'تم حظر هذا الحساب من قِبل إدارة المنصة. يرجى التواصل مع الدعم الفني.',
        ]);
        $this->assertGuest();
    }

    public function test_unbanned_user_can_access_protected_routes_normally(): void
    {
        $user = User::factory()->create([
            'is_banned' => false,
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_banning_a_user_rotates_remember_token_and_blocks_further_access(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'onboarded_at' => now(),
        ]);

        $target = User::factory()->create([
            'is_banned' => false,
            'remember_token' => 'original-token',
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-ban', $target));

        $response->assertRedirect();
        $this->assertTrue($target->fresh()->is_banned);
        $this->assertNotEquals('original-token', $target->fresh()->remember_token);

        // Subsequent request by target is blocked
        $blockedResponse = $this->actingAs($target->fresh())->get(route('dashboard'));
        $blockedResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
