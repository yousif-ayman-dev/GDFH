<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'freelancer_id' => User::factory(),
            'cover_letter' => fake()->paragraph(),
            'bid_amount' => fake()->randomFloat(2, 200, 5000),
            'delivery_days' => fake()->numberBetween(3, 30),
            'status' => 'pending',
        ];
    }
}
