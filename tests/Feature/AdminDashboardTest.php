<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at'  => now(),
            'username'      => 'admin_' . strtolower(Str::random(6)),
            'account_type'  => 'client',
            'is_admin'      => true,
        ], $attrs));
    }

    protected function createUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at'  => now(),
            'username'      => 'user_' . strtolower(Str::random(6)),
            'account_type'  => 'client',
            'is_admin'      => false,
        ], $attrs));
    }

    // ─── Access Control ──────────────────────────────────────────────────────

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.index'));
        $response->assertStatus(200);
        $response->assertSee('لوحة تحكم');
    }

    public function test_non_admin_is_forbidden_from_admin_dashboard(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get(route('admin.index'));
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_users_management_page(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.users'));
        $response->assertStatus(200);
    }

    public function test_non_admin_is_forbidden_from_users_management(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get(route('admin.users'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_projects_management_page(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.projects'));
        $response->assertStatus(200);
    }

    public function test_non_admin_is_forbidden_from_projects_management(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get(route('admin.projects'));
        $response->assertStatus(403);
    }

    // ─── System Stats ─────────────────────────────────────────────────────────

    public function test_admin_dashboard_shows_system_stats(): void
    {
        $admin = $this->createAdmin();
        $this->createUser(['account_type' => 'freelancer']);

        $response = $this->actingAs($admin)->get(route('admin.index'));
        $response->assertStatus(200);
        $response->assertSee('إجمالي المستخدمين');
        $response->assertSee('إجمالي المشاريع');
    }

    // ─── User Management Actions ──────────────────────────────────────────────

    public function test_admin_can_grant_admin_privilege_to_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createUser();

        $this->assertFalse($target->is_admin);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $target));

        $response->assertRedirect();
        $this->assertTrue($target->fresh()->is_admin);
    }

    public function test_admin_can_revoke_admin_privilege_from_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin(); // create another admin

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $target));

        $response->assertRedirect();
        $this->assertFalse($target->fresh()->is_admin);
    }

    public function test_admin_cannot_toggle_own_admin_status(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        // Admin status unchanged
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_admin_can_ban_a_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createUser(['account_type' => 'client']);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-ban', $target));

        $response->assertRedirect();
        $this->assertTrue($target->fresh()->is_banned);
    }

    public function test_admin_can_unban_a_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createUser(['is_banned' => true]);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-ban', $target));

        $response->assertRedirect();
        $this->assertFalse($target->fresh()->is_banned);
    }

    public function test_admin_cannot_ban_another_admin(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-ban', $target));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        // is_banned unchanged
        $this->assertFalse($target->fresh()->is_banned);
    }

    public function test_admin_cannot_ban_self(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.users.toggle-ban', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_non_admin_cannot_toggle_admin_status(): void
    {
        $user = $this->createUser();
        $target = $this->createUser();

        $response = $this->actingAs($user)
            ->post(route('admin.users.toggle-admin', $target));

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_ban_users(): void
    {
        $user = $this->createUser();
        $target = $this->createUser();

        $response = $this->actingAs($user)
            ->post(route('admin.users.toggle-ban', $target));

        $response->assertStatus(403);
    }

    // ─── User Search ──────────────────────────────────────────────────────────

    public function test_admin_can_search_users_by_name(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createUser(['name' => 'محمد إبراهيم']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users', ['search' => 'محمد']));

        $response->assertStatus(200);
        $response->assertSee('محمد إبراهيم');
    }

    public function test_admin_can_filter_users_by_type(): void
    {
        $admin = $this->createAdmin();
        $freelancer = $this->createUser(['account_type' => 'freelancer', 'name' => 'مستقل تجريبي']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users', ['type' => 'freelancer']));

        $response->assertStatus(200);
        $response->assertSee('مستقل تجريبي');
    }

    // ─── Projects List ────────────────────────────────────────────────────────

    public function test_admin_can_see_all_projects(): void
    {
        $admin = $this->createAdmin();
        $owner = $this->createUser();
        Project::factory()->create([
            'owner_id'   => $owner->id,
            'title'      => 'مشروع اختبار الأدمن',
            'visibility' => 'public',
            'status'     => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.projects'));
        $response->assertStatus(200);
        $response->assertSee('مشروع اختبار الأدمن');
    }

    // ─── is_admin Flag ────────────────────────────────────────────────────────

    public function test_is_admin_defaults_to_false_for_new_users(): void
    {
        $user = $this->createUser();
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_returns_true_for_admin_users(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue($admin->isAdmin());
    }
}
