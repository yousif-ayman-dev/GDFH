<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityTimelineEngineTest extends TestCase
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

    public function test_activity_service_records_activities_for_all_platform_events(): void
    {
        $user = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $project = Project::factory()->create(['owner_id' => $user->id, 'team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'created_by' => $user->id]);

        $service = app(ActivityService::class);

        // Record team created
        $service->logTeamCreated($user, $team);
        $this->assertDatabaseHas('activities', [
            'event' => 'team_created',
            'user_id' => $user->id,
            'subject_id' => $team->id,
        ]);

        // Record project created
        $service->logProjectCreated($user, $project);
        $this->assertDatabaseHas('activities', [
            'event' => 'project_created',
            'user_id' => $user->id,
            'subject_id' => $project->id,
        ]);

        // Record project status changed
        $service->logProjectStatusChanged($user, $project, 'draft', 'open');
        $this->assertDatabaseHas('activities', [
            'event' => 'project_status_changed',
            'user_id' => $user->id,
            'subject_id' => $project->id,
        ]);

        // Record task created
        $service->logTaskCreated($user, $task);
        $this->assertDatabaseHas('activities', [
            'event' => 'task_created',
            'user_id' => $user->id,
            'subject_id' => $task->id,
        ]);
    }

    public function test_activities_are_ordered_newest_first(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $activity1 = Activity::create([
            'user_id' => $user->id,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project_created',
            'description' => 'First Activity',
            'created_at' => now()->subMinutes(10),
        ]);

        $activity2 = Activity::create([
            'user_id' => $user->id,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project_status_changed',
            'description' => 'Second Activity',
            'created_at' => now(),
        ]);

        $latestActivities = Activity::where('subject_type', Project::class)
            ->where('subject_id', $project->id)
            ->latest('id')
            ->get();

        $this->assertEquals($activity2->id, $latestActivities->first()->id);
    }

    public function test_project_workspace_renders_activity_timeline(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Activity::create([
            'user_id' => $user->id,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'event' => 'project_created',
            'description' => 'أنشأ المشرف مشروعاً جديداً',
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('سجل الأنشطة والأحداث');
        $response->assertSee('أنشأ المشرف مشروعاً جديداً');
    }
}
