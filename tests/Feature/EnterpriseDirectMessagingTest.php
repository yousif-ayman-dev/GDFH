<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseDirectMessagingTest extends TestCase
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

    public function test_messaging_center_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('messaging.index'));

        $response->assertStatus(200);
        $response->assertSee('الرسائل والمحادثات المباشرة');
    }

    public function test_unauthenticated_user_cannot_access_messaging(): void
    {
        $response = $this->get(route('messaging.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_can_start_or_retrieve_conversation_between_two_users(): void
    {
        $userA = $this->createOnboardedUser();
        $userB = $this->createOnboardedUser();

        $response = $this->actingAs($userA)->post(route('messaging.start', $userB));

        $response->assertRedirect();
        $this->assertDatabaseHas('conversations', [
            'user_one_id' => min($userA->id, $userB->id),
            'user_two_id' => max($userA->id, $userB->id),
        ]);
    }

    public function test_user_cannot_start_conversation_with_self(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->post(route('messaging.start', $user));

        $response->assertSessionHasErrors('messaging');
    }

    public function test_user_can_send_direct_message(): void
    {
        $userA = $this->createOnboardedUser();
        $userB = $this->createOnboardedUser();

        $service = app(MessagingService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);

        $response = $this->actingAs($userA)->post(route('messaging.send', $conv), [
            'content' => 'Hello, I am interested in working with you!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'sender_id' => $userA->id,
            'content' => 'Hello, I am interested in working with you!',
        ]);
    }

    public function test_opening_conversation_marks_unread_messages_as_read(): void
    {
        $userA = $this->createOnboardedUser();
        $userB = $this->createOnboardedUser();

        $service = app(MessagingService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);
        $msg = $service->sendMessage($conv, $userA, 'Unread message test');

        $this->assertNull($msg->fresh()->read_at);

        $response = $this->actingAs($userB)->get(route('messaging.index', ['conversation_id' => $conv->id]));

        $response->assertStatus(200);
        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_unauthorized_user_cannot_access_or_send_messages_in_other_users_conversation(): void
    {
        $userA = $this->createOnboardedUser();
        $userB = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $service = app(MessagingService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);

        $response = $this->actingAs($stranger)->post(route('messaging.send', $conv), [
            'content' => 'Unauthorized message attempt.',
        ]);

        $response->assertStatus(403);
    }
}
