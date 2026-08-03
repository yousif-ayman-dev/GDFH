<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        return [
            'user_one_id' => min($u1->id, $u2->id),
            'user_two_id' => max($u1->id, $u2->id),
            'last_message_at' => now(),
        ];
    }
}
