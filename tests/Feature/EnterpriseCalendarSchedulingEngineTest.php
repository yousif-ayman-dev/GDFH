<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\CalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseCalendarSchedulingEngineTest extends TestCase
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

    public function test_calendar_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('calendar.index'));

        $response->assertStatus(200);
        $response->assertSee('التقويم والمواعيد');
        $response->assertSee('الشهر');
        $response->assertSee('الأسبوع');
        $response->assertSee('الأجندة');
        $response->assertSee('حدث جديد');
    }

    public function test_unauthenticated_user_cannot_access_calendar(): void
    {
        $response = $this->get(route('calendar.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_event_generation_from_project_and_task_dates(): void
    {
        $user = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'title' => 'Project Calendar Alpha',
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'deadline' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Task Deadline Beta',
            'due_at' => now()->addDays(2),
            'assigned_to' => $user->id,
        ]);

        $calendarService = app(CalendarService::class);
        $events = $calendarService->getEvents($user);

        $this->assertGreaterThanOrEqual(3, $events->count());
        $this->assertTrue($events->contains(fn ($evt) => str_contains($evt['title'], 'Project Calendar Alpha')));
        $this->assertTrue($events->contains(fn ($evt) => str_contains($evt['title'], 'Task Deadline Beta')));
    }

    public function test_month_grid_and_date_accuracy(): void
    {
        $user = $this->createOnboardedUser();

        $calendarService = app(CalendarService::class);
        $grid = $calendarService->getCalendarGrid($user, now()->format('Y-m'));

        $this->assertArrayHasKey('days', $grid);
        $this->assertGreaterThanOrEqual(28, count($grid['days']));
        $this->assertArrayHasKey('events_count', $grid);
    }

    public function test_calendar_filtering_by_type_and_assigned_to_me(): void
    {
        $user = $this->createOnboardedUser();
        $otherUser = $this->createOnboardedUser();

        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'My Assigned Task Event',
            'assigned_to' => $user->id,
            'due_at' => now()->addDay(),
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Other Member Task Event',
            'assigned_to' => $otherUser->id,
            'due_at' => now()->addDay(),
        ]);

        $calendarService = app(CalendarService::class);

        $allTaskEvents = $calendarService->getEvents($user, ['type' => 'task']);
        $myTaskEvents = $calendarService->getEvents($user, ['type' => 'task', 'assigned_to_me' => 1]);

        $this->assertEquals(2, $allTaskEvents->count());
        $this->assertEquals(1, $myTaskEvents->count());
        $this->assertStringContainsString('My Assigned Task Event', $myTaskEvents->first()['title']);
    }

    public function test_calendar_workspace_authorization_isolation(): void
    {
        $user1 = $this->createOnboardedUser();
        $user2 = $this->createOnboardedUser();

        Project::factory()->create([
            'owner_id' => $user1->id,
            'title' => 'User 1 Private Project Event',
            'deadline' => now()->addDays(5),
        ]);

        $calendarService = app(CalendarService::class);
        $user2Events = $calendarService->getEvents($user2);

        $this->assertFalse($user2Events->contains(fn ($evt) => str_contains($evt['title'], 'User 1 Private Project Event')));
    }

    public function test_authenticated_user_can_create_custom_calendar_event(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->post(route('calendar.events.store'), [
            'title' => 'اجتماع تخطيط الفريق',
            'description' => 'مناقشة خطة الربع القادم',
            'start_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'color' => 'blue',
            'location' => 'غرفة الاجتماعات 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'title' => 'اجتماع تخطيط الفريق',
            'color' => 'blue',
        ]);
    }

    public function test_custom_events_appear_in_calendar_service(): void
    {
        $user = $this->createOnboardedUser();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'حدث مخصص متميز',
            'start_at' => now()->addDays(3),
        ]);

        $calendarService = app(CalendarService::class);
        $events = $calendarService->getEvents($user);

        $this->assertTrue($events->contains(fn ($evt) => $evt['title'] === 'حدث مخصص متميز'));
    }

    public function test_user_can_update_own_calendar_event(): void
    {
        $user = $this->createOnboardedUser();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'عنوان قديم',
        ]);

        $response = $this->actingAs($user)->put(route('calendar.events.update', $event), [
            'title' => 'عنوان معدل جديد',
            'start_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'color' => 'emerald',
        ]);

        $response->assertRedirect();
        $this->assertEquals('عنوان معدل جديد', $event->fresh()->title);
        $this->assertEquals('emerald', $event->fresh()->color);
    }

    public function test_user_can_delete_own_calendar_event(): void
    {
        $user = $this->createOnboardedUser();
        $event = Event::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('calendar.events.destroy', $event));

        $response->assertRedirect();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_user_cannot_update_or_delete_other_users_event(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $event = Event::factory()->create(['user_id' => $owner->id, 'title' => 'حدث أصلي']);

        $updateResponse = $this->actingAs($stranger)->put(route('calendar.events.update', $event), [
            'title' => 'محاولة اختراق',
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $updateResponse->assertStatus(403);
        $this->assertEquals('حدث أصلي', $event->fresh()->title);

        $deleteResponse = $this->actingAs($stranger)->delete(route('calendar.events.destroy', $event));
        $deleteResponse->assertStatus(403);
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }
}
