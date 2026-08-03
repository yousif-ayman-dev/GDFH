<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->user_one_id === (int) $user->id ||
               (int) $conversation->user_two_id === (int) $user->id;
    }

    /**
     * Determine whether the user can send a message in the conversation.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->user_one_id === (int) $user->id ||
               (int) $conversation->user_two_id === (int) $user->id;
    }
}
