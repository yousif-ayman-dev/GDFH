<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_at' => now()->addDays(rand(1, 10)),
            'end_at' => now()->addDays(rand(1, 10))->addHours(2),
            'color' => $this->faker->randomElement(['copper', 'blue', 'amber', 'purple', 'emerald', 'red']),
            'type' => 'event',
            'location' => $this->faker->city(),
        ];
    }
}
