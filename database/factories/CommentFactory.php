<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $body = fake()->sentence(8);

        return [
            'user_id' => User::factory(),
            'commentable_type' => Project::class,
            'commentable_id' => Project::factory(),
            'parent_id' => null,
            'body' => $body,
            'content' => $body,
            'edited_at' => null,
        ];
    }
}
