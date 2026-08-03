<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worklog>
 */
class WorklogFactory extends Factory
{
    protected $model = Worklog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'task_id' => Task::factory(),
            'start_time' => now()->subHours(2),
            'end_time' => now(),
            'duration' => 7200, // 2 hours in seconds
            'status' => 'stopped',
            'notes' => fake()->sentence(),
            'is_billable' => true,
            'is_manual' => false,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'running',
            'start_time' => now(),
            'end_time' => null,
            'duration' => 0,
        ]);
    }
}
