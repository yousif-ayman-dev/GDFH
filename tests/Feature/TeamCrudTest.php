<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_teams(): void
    {
        $response = $this->get(route('teams.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_their_teams(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownedTeam = $this->createTeam($owner, ['name' => 'Alpha Team']);
        $otherTeam = $this->createTeam($otherUser, ['name' => 'Beta Team']);

        $response = $this->actingAs($owner)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSee($ownedTeam->name);
        $response->assertDontSee($otherTeam->name);
    }

    public function test_user_can_create_valid_team(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('teams.store'), [
            'name' => 'Launch Team',
            'description' => 'Handles releases.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'owner_id' => $owner->id,
            'name' => 'Launch Team',
            'description' => 'Handles releases.',
            'type' => 'permanent',
            'visibility' => 'private',
            'logo_path' => null,
        ]);
    }

    public function test_user_can_create_team_with_valid_logo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $logo = $this->fakeImageUpload('logo.png');

        $response = $this->actingAs($owner)->post(route('teams.store'), [
            'name' => 'Design Team',
            'description' => 'Creative work.',
            'type' => 'permanent',
            'visibility' => 'private',
            'logo' => $logo,
        ]);

        $response->assertRedirect();
        $team = Team::query()->where('name', 'Design Team')->firstOrFail();

        $this->assertNotNull($team->logo_path);
        Storage::disk('public')->assertExists($team->logo_path);
    }

    public function test_invalid_logo_upload_is_rejected(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $logo = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($owner)->from(route('teams.create'))->post(route('teams.store'), [
            'name' => 'Bad Logo Team',
            'description' => 'Should fail.',
            'type' => 'permanent',
            'visibility' => 'private',
            'logo' => $logo,
        ]);

        $response->assertRedirect(route('teams.create'));
        $response->assertSessionHasErrors(['logo']);
        $this->assertDatabaseMissing('teams', [
            'owner_id' => $owner->id,
            'name' => 'Bad Logo Team',
        ]);
    }

    public function test_invalid_team_data_is_rejected(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->from(route('teams.create'))->post(route('teams.store'), [
            'name' => '',
            'type' => 'invalid',
            'visibility' => 'private',
        ]);

        $response->assertRedirect(route('teams.create'));
        $response->assertSessionHasErrors(['name', 'type']);
        $this->assertDatabaseMissing('teams', [
            'owner_id' => $owner->id,
        ]);
    }

    public function test_owner_can_view_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Ops Team']);

        $response = $this->actingAs($owner)->get(route('teams.show', $team));

        $response->assertOk();
        $response->assertSee($team->name);
    }

    public function test_non_owner_cannot_view_private_team_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Ops Team']);

        $response = $this->actingAs($otherUser)->get(route('teams.show', $team));

        $response->assertForbidden();
    }

    public function test_owner_can_edit_and_update_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Old Team']);

        $editResponse = $this->actingAs($owner)->get(route('teams.edit', $team));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($owner)->patch(route('teams.update', $team), [
            'name' => 'Updated Team',
            'description' => 'Refined.',
            'type' => 'project_based',
            'visibility' => 'public',
        ]);

        $updateResponse->assertRedirect(route('teams.show', $team));
        $team->refresh();

        $this->assertSame('Updated Team', $team->name);
        $this->assertSame('Refined.', $team->description);
        $this->assertSame('project_based', $team->type);
        $this->assertSame('public', $team->visibility);
        $this->assertNotSame('old-team', $team->slug);
    }

    public function test_non_owner_cannot_update_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Visible Team']);

        $response = $this->actingAs($otherUser)->patch(route('teams.update', $team), [
            'name' => 'Changed Name',
        ]);

        $response->assertForbidden();
        $team->refresh();
        $this->assertSame('Visible Team', $team->name);
    }

    public function test_owner_can_replace_team_logo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Image Team']);
        $initialLogo = $this->fakeImageUpload('initial.png');
        $replacementLogo = $this->fakeImageUpload('replacement.png');

        $team->update(['logo_path' => $initialLogo->store('teams', 'public')]);

        $response = $this->actingAs($owner)->patch(route('teams.update', $team), [
            'name' => $team->name,
            'type' => $team->type,
            'visibility' => $team->visibility,
            'logo' => $replacementLogo,
        ]);

        $response->assertRedirect(route('teams.show', $team));
        $team->refresh();

        $this->assertNotNull($team->logo_path);
        $this->assertNotSame('teams/initial.png', $team->logo_path);
        Storage::disk('public')->assertExists($team->logo_path);
        Storage::disk('public')->assertMissing('teams/initial.png');
    }

    public function test_owner_can_link_project_to_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Workspace Team']);
        $project = $this->createProject($owner, ['title' => 'Linked Project']);

        $response = $this->actingAs($owner)->post(route('teams.projects.attach', [$team, $project]));

        $response->assertRedirect(route('teams.show', $team));
        $this->assertDatabaseHas('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_duplicate_team_project_link_is_rejected(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Duplicate Team']);
        $project = $this->createProject($owner, ['title' => 'Duplicate Project']);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);

        $response = $this->actingAs($owner)->from(route('teams.show', $team))->post(route('teams.projects.attach', [$team, $project]));

        $response->assertRedirect(route('teams.show', $team));
        $response->assertSessionHasErrors('project_id');
    }

    public function test_owner_can_unlink_project_from_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Unlink Team']);
        $project = $this->createProject($owner, ['title' => 'Unlink Project']);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);

        $response = $this->actingAs($owner)->delete(route('teams.projects.detach', [$team, $project]));

        $response->assertRedirect(route('teams.show', $team));
        $this->assertDatabaseMissing('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_owner_can_delete_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Delete Me']);

        $response = $this->actingAs($owner)->delete(route('teams.destroy', $team));

        $response->assertRedirect(route('teams.index'));
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_non_owner_cannot_delete_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Protected Team']);

        $response = $this->actingAs($otherUser)->delete(route('teams.destroy', $team));

        $response->assertForbidden();
        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    public function test_deleting_team_cleans_up_stored_logo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Cleanup Team']);
        $logoPath = 'teams/logo.png';
        Storage::disk('public')->put($logoPath, 'content');
        $team->update(['logo_path' => $logoPath]);

        $response = $this->actingAs($owner)->delete(route('teams.destroy', $team));

        $response->assertRedirect(route('teams.index'));
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_slug_is_generated_and_changes_when_name_changes(): void
    {
        $owner = User::factory()->create();

        $team = $this->createTeam($owner, ['name' => 'Finance Team']);
        $this->assertMatchesRegularExpression('/^finance-team-[a-z]{6}$/', $team->slug);

        $team->update(['name' => 'Operations Team']);
        $team->refresh();
        $this->assertMatchesRegularExpression('/^operations-team-[a-z]{6}$/', $team->slug);
    }

    private function createTeam(User $owner, array $attributes = []): Team
    {
        $data = [
            'owner_id' => $owner->id,
            'name' => $attributes['name'] ?? 'Sample Team',
            'description' => $attributes['description'] ?? null,
            'type' => $attributes['type'] ?? 'permanent',
            'visibility' => $attributes['visibility'] ?? 'private',
        ];

        if (array_key_exists('slug', $attributes)) {
            $data['slug'] = $attributes['slug'];
        }

        return Team::create($data);
    }

    private function createProject(User $owner, array $attributes = []): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => $attributes['title'] ?? 'Sample Project',
            'slug' => ($attributes['slug'] ?? 'sample-project') . '-' . uniqid(),
            'description' => $attributes['description'] ?? 'Sample project',
            'visibility' => $attributes['visibility'] ?? 'private',
            'status' => $attributes['status'] ?? 'draft',
            'currency' => $attributes['currency'] ?? 'USD',
        ]);
    }

    private function fakeImageUpload(string $name): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQABAA4A1GvVxQAAAABJRU5ErkJggg==');
        $path = tempnam(sys_get_temp_dir(), 'team-logo');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
