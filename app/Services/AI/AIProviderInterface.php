<?php

namespace App\Services\AI;

use App\Models\User;

interface AIProviderInterface
{
    /**
     * Generate an AI response for a user prompt.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string;

    /**
     * Analyze user workspace and generate health scores, insights, strengths, weaknesses, and recommendations.
     *
     * @return array<string, mixed>
     */
    public function analyzeWorkspace(User $user): array;
}
