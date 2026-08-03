<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'proposal_id' => Proposal::factory(),
            'client_id' => User::factory(),
            'freelancer_id' => User::factory(),
            'title' => 'Contract Agreement: ' . fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'status' => 'active',
            'start_date' => now(),
            'end_date' => null,
            'completed_at' => null,
        ];
    }
}
