<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_onboarding_page(): void
    {
        $response = $this->get(route('onboarding'));

        $response->assertRedirect(route('login'));
    }

    public function test_newly_registered_user_is_redirected_to_onboarding_when_accessing_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding'));
    }

    public function test_user_can_access_onboarding_page_when_not_onboarded(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertStatus(200);
        $response->assertSee('مرحباً بك في Tasker!');
    }

    public function test_onboarded_user_is_redirected_to_dashboard_when_visiting_onboarding(): void
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'account_type' => 'freelancer',
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_freelancer_onboarding_successful(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'freelancer',
            'username' => 'John_Dev',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertEquals('john_dev', $user->username);
        $this->assertEquals('freelancer', $user->account_type);
        $this->assertNotNull($user->onboarded_at);
        $this->assertTrue($user->isFreelancer());
        $this->assertFalse($user->isClient());
        $this->assertTrue($user->hasCompletedOnboarding());
    }

    public function test_client_onboarding_successful(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'client',
            'username' => 'Client_Corp',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertEquals('client_corp', $user->username);
        $this->assertEquals('client', $user->account_type);
        $this->assertNotNull($user->onboarded_at);
        $this->assertTrue($user->isClient());
        $this->assertFalse($user->isFreelancer());
        $this->assertTrue($user->hasCompletedOnboarding());
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'taken_username',
            'account_type' => 'freelancer',
            'onboarded_at' => now(),
        ]);

        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'freelancer',
            'username' => 'TAKEN_USERNAME',
        ]);

        $response->assertSessionHasErrors(['username']);
    }

    public function test_username_validation_rules(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        // Invalid characters (spaces, special chars)
        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'freelancer',
            'username' => 'invalid username!',
        ]);

        $response->assertSessionHasErrors(['username']);

        // Too short (< 3 chars)
        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'freelancer',
            'username' => 'ab',
        ]);

        $response->assertSessionHasErrors(['username']);
    }

    public function test_invalid_account_type_is_rejected(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_type' => 'admin',
            'username' => 'valid_user',
        ]);

        $response->assertSessionHasErrors(['account_type']);
    }

    public function test_unonboarded_user_can_still_access_logout(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_existing_user_compatibility_and_model_helpers(): void
    {
        $existingUser = User::factory()->create([
            'username' => null,
            'account_type' => null,
            'onboarded_at' => null,
        ]);

        $this->assertFalse($existingUser->hasCompletedOnboarding());
        $this->assertFalse($existingUser->isFreelancer());
        $this->assertFalse($existingUser->isClient());

        // Completing onboarding
        $existingUser->update([
            'username' => 'legacy_user',
            'account_type' => 'freelancer',
            'onboarded_at' => now(),
        ]);

        $this->assertTrue($existingUser->hasCompletedOnboarding());
        $this->assertTrue($existingUser->isFreelancer());
    }

    public function test_full_registration_to_onboarding_to_dashboard_and_logout_flow(): void
    {
        // 1. Register new user
        $registerResponse = $this->post(route('register'), [
            'name' => 'Flow User',
            'email' => 'flow@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        // 2. Access dashboard -> redirected to onboarding
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('onboarding'));

        // 3. Render onboarding screen
        $onboardingViewResponse = $this->get(route('onboarding'));
        $onboardingViewResponse->assertStatus(200);

        // 4. Submit onboarding form
        $onboardingStoreResponse = $this->post(route('onboarding.store'), [
            'account_type' => 'freelancer',
            'username' => 'flow_user_123',
        ]);

        $onboardingStoreResponse->assertRedirect(route('dashboard'));

        // 5. Access dashboard -> succeeds
        $finalDashboardResponse = $this->get(route('dashboard'));
        $finalDashboardResponse->assertStatus(200);

        // 6. Logout -> succeeds
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_get_logout_is_not_supported_as_get_route(): void
    {
        $user = User::factory()->create();

        // Direct GET request to /logout should not execute a logout action
        $response = $this->actingAs($user)->get('/logout');

        // Route /logout only accepts POST; GET should return 405 Method Not Allowed or 404
        $this->assertTrue(in_array($response->getStatusCode(), [404, 405, 302]));
        
        // User remains authenticated because GET /logout does not process logout
        $this->assertAuthenticatedAs($user);
    }
}
