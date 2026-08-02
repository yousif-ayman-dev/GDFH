<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'team_id' => null,
            'owner_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . strtolower(Str::random(6)),
            'description' => fake()->paragraph(),
            'category' => 'Web Development',
            'visibility' => 'private',
            'status' => 'draft',
            'budget' => fake()->randomFloat(2, 500, 10000),
            'currency' => 'USD',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'deadline' => now()->addDays(30)->toDateString(),
            'archived_at' => null,
        ];
    }

    /**
     * Active status.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'published_at' => now(),
        ]);
    }

    /**
     * Archived state.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }
}
