<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(\Illuminate\Support\Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_guest_cannot_access_projects(): void
    {
        $response = $this->get(route('projects.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_projects_index(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user)
            ->get(route('projects.index'));

        $response->assertOk();
        $response->assertViewIs('projects.index');
    }

    public function test_user_can_create_a_project(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user)
            ->post(route('projects.store'), [
                'title' => 'Freelance Platform',
                'description' => 'Arabic freelance marketplace platform.',
                'category' => 'Web Development',
                'visibility' => 'marketplace',
                'budget_type' => 'fixed',
                'budget_min' => 500,
                'budget_max' => 1000,
                'currency' => 'USD',
                'start_date' => now()->addDay()->format('Y-m-d'),
                'deadline' => now()->addDays(30)->format('Y-m-d'),
            ]);

        $project = Project::first();

        $this->assertNotNull($project);

        $this->assertSame($user->id, $project->owner_id);
        $this->assertSame('Freelance Platform', $project->title);
        $this->assertSame('draft', $project->status);
        $this->assertSame('marketplace', $project->visibility);
        $this->assertNotEmpty($project->slug);

        $response->assertRedirect(
            route('projects.show', $project)
        );
    }

    public function test_project_creation_requires_valid_data(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user)
            ->from(route('projects.create'))
            ->post(route('projects.store'), [
                'title' => '',
                'description' => '',
                'visibility' => 'invalid',
                'budget_type' => 'invalid',
                'budget_min' => -100,
                'budget_max' => -200,
                'currency' => 'US',
                'start_date' => '2026-09-01',
                'deadline' => '2026-08-01',
            ]);

        $response->assertRedirect(route('projects.create'));

        $response->assertSessionHasErrors([
            'title',
            'description',
            'visibility',
            'budget_type',
            'budget_min',
            'budget_max',
            'currency',
            'deadline',
        ]);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_owner_can_view_their_project(): void
    {
        $user = $this->createUser();

        $project = $this->createProject($user);

        $response = $this
            ->actingAs($user)
            ->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertViewIs('projects.show');
        $response->assertViewHas('project', $project);
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($otherUser)
            ->get(route('projects.show', $project));

        $response->assertForbidden();
    }

    public function test_owner_can_update_their_project(): void
    {
        $user = $this->createUser();

        $project = $this->createProject($user);

        $oldSlug = $project->slug;

        $response = $this
            ->actingAs($user)
            ->put(route('projects.update', $project), [
                'title' => 'Updated Project',
                'description' => 'Updated project description.',
                'category' => 'Design',
                'visibility' => 'private',
                'budget_type' => 'hourly',
                'budget_min' => 20,
                'budget_max' => 50,
                'currency' => 'USD',
                'start_date' => now()->addDays(5)->format('Y-m-d'),
                'deadline' => now()->addDays(40)->format('Y-m-d'),
            ]);

        $project->refresh();

        $response->assertRedirect(
            route('projects.show', $project)
        );

        $this->assertSame('Updated Project', $project->title);
        $this->assertSame('Design', $project->category);
        $this->assertSame('hourly', $project->budget_type);
        $this->assertNotSame($oldSlug, $project->slug);
    }

    public function test_historical_project_update_without_modifying_past_start_date_succeeds(): void
    {
        $user = $this->createUser();
        $pastDate = now()->subMonth()->format('Y-m-d');

        $project = Project::create([
            'owner_id' => $user->id,
            'title' => 'Historical Project',
            'slug' => 'historical-proj-' . uniqid(),
            'description' => 'Description',
            'start_date' => $pastDate,
            'visibility' => 'private',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->put(route('projects.update', $project), [
            'title' => 'Renamed Historical Project',
            'start_date' => $pastDate, // Unchanged historical date
        ]);

        $response->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertSame('Renamed Historical Project', $project->title);
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($otherUser)
            ->put(route('projects.update', $project), [
                'title' => 'Unauthorized Update',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
            'title' => 'Unauthorized Update',
        ]);
    }

    public function test_owner_can_delete_their_project(): void
    {
        $user = $this->createUser();

        $project = $this->createProject($user);

        $response = $this
            ->actingAs($user)
            ->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $project = $this->createProject($owner);

        $response = $this
            ->actingAs($otherUser)
            ->delete(route('projects.destroy', $project));

        $response->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Test Project',
            'slug' => 'test-project-' . uniqid(),
            'description' => 'Project used for CRUD testing.',
            'category' => 'Development',
            'visibility' => 'private',
            'status' => 'draft',
            'budget_type' => 'fixed',
            'budget_min' => 100,
            'budget_max' => 500,
            'currency' => 'USD',
            'start_date' => '2026-08-01',
            'deadline' => '2026-09-01',
        ]);
    }
}
