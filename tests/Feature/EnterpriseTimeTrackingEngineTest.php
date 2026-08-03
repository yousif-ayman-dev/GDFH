<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use App\Services\TimeTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseTimeTrackingEngineTest extends TestCase
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

    public function test_time_tracking_page_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('time-tracking.index'));

        $response->assertStatus(200);
        $response->assertSee('تتبع الوقت وسجلات العمل');
        $response->assertSee('المؤقت المباشر');
    }

    public function test_unauthenticated_user_cannot_access_time_tracking(): void
    {
        $response = $this->get(route('time-tracking.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_timer_lifecycle_start_pause_resume_stop(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $service = app(TimeTrackingService::class);

        // 1. Start Timer
        $worklog = $service->startTimer($user, $project, $task, 'Live Testing Notes', true);
        $this->assertEquals('running', $worklog->status);
        $this->assertTrue($worklog->isRunning());

        // 2. Pause Timer
        $worklog = $service->pauseTimer($worklog);
        $this->assertEquals('paused', $worklog->status);
        $this->assertTrue($worklog->isPaused());

        // 3. Resume Timer
        $worklog = $service->resumeTimer($worklog);
        $this->assertEquals('running', $worklog->status);

        // 4. Stop Timer
        $worklog = $service->stopTimer($worklog, 'Finished live work');
        $this->assertEquals('stopped', $worklog->status);
        $this->assertNotNull($worklog->end_time);
    }

    public function test_manual_worklog_creation(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('time-tracking.manual'), [
            'project_id' => $project->id,
            'duration_minutes' => 90,
            'notes' => 'Manual entry 90 mins test',
            'is_billable' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('worklogs', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'duration' => 5400, // 90 * 60 seconds
            'is_manual' => true,
        ]);
    }

    public function test_worklog_duration_and_tracked_time_calculations(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        Worklog::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'duration' => 3600, // 1 hour
        ]);

        Worklog::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'duration' => 1800, // 30 mins
        ]);

        $service = app(TimeTrackingService::class);

        $this->assertEquals(5400, $service->totalTrackedTime($user));
        $this->assertEquals(5400, $service->projectTrackedTime($project));
        $this->assertEquals(5400, $service->taskTrackedTime($task));
    }

    public function test_weekly_and_monthly_summaries(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Worklog::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'duration' => 7200, // 2 hours
            'is_billable' => true,
            'created_at' => now(),
        ]);

        $service = app(TimeTrackingService::class);
        $weekly = $service->weeklySummary($user);
        $monthly = $service->monthlySummary($user);

        $this->assertEquals(2.0, $weekly['total_hours']);
        $this->assertEquals(100, $weekly['billable_percentage']);
        $this->assertEquals(2.0, $monthly['total_hours']);
    }

    public function test_unauthorized_user_cannot_manage_other_user_worklog(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $worklog = Worklog::factory()->create([
            'user_id' => $owner->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($stranger)->delete(route('time-tracking.destroy', $worklog));

        $response->assertStatus(403);
    }
}
