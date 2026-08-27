<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class MultiProviderAIAdapter implements AIProviderInterface
{
    public function __construct(
        protected GeminiAIProvider $geminiProvider,
        protected OpenAIAIProvider $openAIProvider,
        protected RuleBasedAIProvider $ruleBasedProvider
    ) {}

    /**
     * Generate response by trying Providers sequentially (Gemini ➔ OpenAI/Groq ➔ RuleBased).
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        // 1. Primary: Try Gemini AI Provider
        try {
            $geminiResponse = $this->geminiProvider->generateResponse($user, $prompt, $context);
            
            // Check if Gemini returned a rate-limit / quota warning
            if (! str_contains($geminiResponse, 'Quota Exceeded') && ! str_contains($geminiResponse, 'تم الوصول للحد اليومي')) {
                return $geminiResponse;
            }
        } catch (Throwable $e) {
            Log::warning("Gemini AI Provider failed: " . $e->getMessage());
        }

        // 2. Secondary: Try OpenAI / Groq AI Provider if API key is configured
        if (! empty(config('services.openai.api_key'))) {
            try {
                $openAIResponse = $this->openAIProvider->generateResponse($user, $prompt, $context);
                if (! empty($openAIResponse)) {
                    return $openAIResponse;
                }
            } catch (Throwable $e) {
                Log::warning("OpenAI AI Provider failed: " . $e->getMessage());
            }
        }

        // 3. Fallback: Execute Smart Rule-Based Engine
        return $this->ruleBasedProvider->generateResponse($user, $prompt, $context);
    }

    /**
     * Analyze workspace metrics using best available Provider.
     *
     * @return array<string, mixed>
     */
    public function analyzeWorkspace(User $user): array
    {
        return $this->ruleBasedProvider->analyzeWorkspace($user);
    }
}
