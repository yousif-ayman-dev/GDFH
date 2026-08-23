<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseNotificationsMessagingPollingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_poll_unread_notifications_json(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();

        AppNotification::create([
            'user_id' => $user->id,
            'sender_id' => $sender->id,
            'type' => 'system',
            'title' => 'مهمة جديدة',
            'description' => 'تم إسناد مهمة جديدة إليك',
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->getJson(route('notifications.poll'));

        $response->assertOk()
            ->assertJsonStructure([
                'unread_count',
                'notifications' => [
                    '*' => ['id', 'title', 'description', 'type', 'created_at_human', 'sender_name'],
                ],
            ])
            ->assertJson([
                'unread_count' => 1,
            ]);
    }

    public function test_user_can_mark_notification_as_read_via_json(): void
    {
        $user = User::factory()->create();

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'إشعار تذكيري',
            'description' => 'يرجى مراجعة المهام',
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('notifications.read-json', $notification));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read_via_json(): void
    {
        $user = User::factory()->create();

        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'إشعار 1',
            'description' => 'محتوى 1',
        ]);

        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'إشعار 2',
            'description' => 'محتوى 2',
        ]);

        $response = $this->actingAs($user)->postJson(route('notifications.read-all-json'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertEquals(0, $user->appNotifications()->unread()->count());
    }

    public function test_user_cannot_mark_another_users_notification_as_read_json(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $notification = AppNotification::create([
            'user_id' => $user1->id,
            'type' => 'system',
            'title' => 'إشعار خاص',
            'description' => 'محتوى خاص',
        ]);

        $response = $this->actingAs($user2)->postJson(route('notifications.read-json', $notification));

        $response->assertForbidden();
    }

    public function test_conversation_participants_can_poll_messages_json(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'user_one_id' => $user1->id,
            'user_two_id' => $user2->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'content' => 'مرحباً بك!',
        ]);

        $response = $this->actingAs($user2)->getJson(route('messaging.poll', $conversation));

        $response->assertOk()
            ->assertJsonStructure([
                'messages' => [
                    '*' => ['id', 'sender_id', 'sender_name', 'content', 'is_mine', 'created_at_human'],
                ],
            ])
            ->assertJsonFragment([
                'content' => 'مرحباً بك!',
                'is_mine' => false,
            ]);
    }

    public function test_conversation_participant_can_send_message_via_json(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'user_one_id' => $user1->id,
            'user_two_id' => $user2->id,
        ]);

        $response = $this->actingAs($user1)->postJson(route('messaging.send-json', $conversation), [
            'content' => 'رسالة اختبارية جديدة عبر AJAX',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'content' => 'رسالة اختبارية جديدة عبر AJAX',
                'is_mine' => true,
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'content' => 'رسالة اختبارية جديدة عبر AJAX',
        ]);
    }

    public function test_unauthorized_user_cannot_poll_or_send_messages_in_private_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $stranger = User::factory()->create();

        $conversation = Conversation::create([
            'user_one_id' => $user1->id,
            'user_two_id' => $user2->id,
        ]);

        $pollResponse = $this->actingAs($stranger)->getJson(route('messaging.poll', $conversation));
        $pollResponse->assertForbidden();

        $sendResponse = $this->actingAs($stranger)->postJson(route('messaging.send-json', $conversation), [
            'content' => 'محاولة غير مصرحة',
        ]);
        $sendResponse->assertForbidden();
    }
}
