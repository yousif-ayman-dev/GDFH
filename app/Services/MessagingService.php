<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MessagingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get or create a 1-on-1 direct conversation between two users.
     */
    public function getOrCreateConversation(User $userA, User $userB): Conversation
    {
        if ((int) $userA->id === (int) $userB->id) {
            throw new InvalidArgumentException('لا يمكنك بدء محادثة مباشرة مع نفسك.');
        }

        $userOneId = min($userA->id, $userB->id);
        $userTwoId = max($userA->id, $userB->id);

        return Conversation::firstOrCreate([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
        ], [
            'last_message_at' => now(),
        ]);
    }

    /**
     * Get user conversations list with eager loaded relations.
     */
    public function getUserConversations(User $user): Collection
    {
        return Conversation::query()
            ->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    /**
     * Send a direct message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        if ((int) $conversation->user_one_id !== (int) $sender->id && (int) $conversation->user_two_id !== (int) $sender->id) {
            throw new InvalidArgumentException('غير مصرح لك بإرسال رسائل في هذه المحادثة.');
        }

        $detector = new \App\Services\Security\OffPlatformDetectorService();
        $inspection = $detector->inspectAndFilter($content);
        $cleanContent = $inspection['clean_text'];

        return DB::transaction(function () use ($conversation, $sender, $cleanContent) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'content' => $cleanContent,
                'read_at' => null,
            ]);

            $conversation->update([
                'last_message_at' => now(),
            ]);

            $recipient = $conversation->getOtherUser($sender);

            $this->notificationService->sendNotification(
                $recipient,
                'رسالة مباشرة جديدة',
                "أرسل لك {$sender->name} رسالة جديدة: " . \Illuminate\Support\Str::limit($cleanContent, 40),
                route('messaging.index', ['conversation_id' => $conversation->id])
            );

            return $message;
        });
    }

    /**
     * Mark all unread messages in a conversation as read for user.
     */
    public function markConversationAsRead(Conversation $conversation, User $user): void
    {
        Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get total unread messages count for user across all conversations.
     */
    public function unreadTotalCount(User $user): int
    {
        $conversationIds = Conversation::query()
            ->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->pluck('id');

        return Message::query()
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
