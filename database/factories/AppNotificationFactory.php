<?php

namespace Database\Factories;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppNotification>
 */
class AppNotificationFactory extends Factory
{
    protected $model = AppNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sender_id' => User::factory(),
            'type' => 'task_assigned',
            'title' => 'تم إسناد مهمة جديدة إليك',
            'description' => 'تم إسناد المهمة لتنفيذ المشروع',
            'action_url' => '/dashboard',
            'priority' => 'normal',
            'data' => [],
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }
}
