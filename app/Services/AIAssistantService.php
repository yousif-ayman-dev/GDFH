<?php

namespace App\Services;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\User;
use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AIAssistantService
{
    public function __construct(
        protected AIProviderInterface $aiProvider
    ) {}

    /**
     * Get workspace analysis insights and health score.
     *
     * @return array<string, mixed>
     */
    public function getWorkspaceAnalysis(User $user): array
    {
        return $this->aiProvider->analyzeWorkspace($user);
    }

    /**
     * Get user conversations list.
     */
    public function getConversations(User $user): Collection
    {
        return AIConversation::query()
            ->where('user_id', $user->id)
            ->withCount('messages')
            ->latest()
            ->get();
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(User $user, ?string $title = null): AIConversation
    {
        return AIConversation::create([
            'user_id' => $user->id,
            'title' => $title ?? ('محادثة ' . now()->format('Y-m-d H:i')),
        ]);
    }

    /**
     * Send user message in conversation and get AI response.
     */
    public function sendMessage(AIConversation $conversation, string $userMessage): AIMessage
    {
        return DB::transaction(function () use ($conversation, $userMessage) {
            // 1. Store User Message
            $userMsg = AIMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);

            // Auto-update conversation title if default
            if (Str::startsWith($conversation->title, 'محادثة ')) {
                $conversation->update([
                    'title' => Str::limit($userMessage, 30),
                ]);
            }

            // 2. Generate AI Response via Provider
            $aiReplyContent = $this->aiProvider->generateResponse($conversation->user, $userMessage, [
                'conversation_id' => $conversation->id,
            ]);

            // 3. Store AI Assistant Response
            $assistantMsg = AIMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $aiReplyContent,
            ]);

            $conversation->touch();

            return $assistantMsg;
        });
    }

    /**
     * Delete conversation.
     */
    public function deleteConversation(AIConversation $conversation): void
    {
        $conversation->delete();
    }
}
