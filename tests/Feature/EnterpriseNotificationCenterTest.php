<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseNotificationCenterTest extends TestCase
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

    public function test_notification_creation_and_listing(): void
    {
        $recipient = $this->createOnboardedUser();
        $sender = $this->createOnboardedUser();

        $notificationService = app(NotificationService::class);
        $notificationService->send(
            $recipient,
            'task_assigned',
            'مهمة جديدة',
            'تم إسناد مهمة جديدة إليك',
            $sender,
            '/dashboard',
            'high'
        );

        $response = $this->actingAs($recipient)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('مركز الإشعارات');
        $response->assertSee('مهمة جديدة');
        $response->assertSee('تم إسناد مهمة جديدة إليك');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = $this->createOnboardedUser();
        $notification = AppNotification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
            'action_url' => null,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        $response->assertRedirect();
        $this->assertTrue($notification->fresh()->isRead());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->createOnboardedUser();
        AppNotification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $this->assertEquals(3, $user->unreadNotificationsCount());

        $response = $this->actingAs($user)->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotificationsCount());
    }

    public function test_user_can_delete_notification(): void
    {
        $user = $this->createOnboardedUser();
        $notification = AppNotification::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('notifications.destroy', $notification));

        $response->assertRedirect();
        $this->assertDatabaseMissing('app_notifications', ['id' => $notification->id]);
    }

    public function test_unauthorized_user_cannot_manage_other_user_notification(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $notification = AppNotification::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger)->delete(route('notifications.destroy', $notification));

        $response->assertStatus(403);
    }

    public function test_unread_counter_calculation(): void
    {
        $user = $this->createOnboardedUser();

        AppNotification::factory()->count(2)->create(['user_id' => $user->id, 'read_at' => null]);
        AppNotification::factory()->read()->create(['user_id' => $user->id]);

        $notificationService = app(NotificationService::class);

        $this->assertEquals(2, $notificationService->unreadCount($user));
    }

    public function test_activity_integration_triggers_notification(): void
    {
        $creator = $this->createOnboardedUser();
        $assignee = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $creator->id]);

        \App\Models\ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $taskService = app(\App\Services\TaskService::class);
        $taskService->createTask($creator, $project, [
            'title' => 'Assigned Task Notification Test',
            'assigned_to' => $assignee->id,
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $assignee->id,
            'type' => 'task_assigned',
        ]);
    }
}
