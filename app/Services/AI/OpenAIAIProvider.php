<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAIAIProvider implements AIProviderInterface
{
    public function __construct(
        protected RuleBasedAIProvider $fallbackProvider
    ) {}

    /**
     * Generate response using OpenAI / Groq REST API.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');

        if (empty($apiKey)) {
            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        }

        try {
            $taskerPlatformContext = <<<SYS
أنت مساعد الذكاء الاصطناعي الخاص والرسمي المتقدم لمنصة "Tasker" (Tasker Platform).
تنبيه هام جداً: منصة Tasker التي نستخدمها هنا هي منصة ويب متكاملة لإدارة المشاريع والفرق والخدمات المصغرة بين العملاء (Clients) والمستقلين (Freelancers)، وليست تطبيق الأتمتة على أندرويد.
تحدث مع المستخدم ({$user->name}) بذكاء وطلاقة وعفوية تامة في أي موضوع (إدارة، برمجة، علوم، ثقافة، نصائح عامة). وإذا سُئلت عن منصة Tasker أجب بناءً على تفاصيل المنصة.
SYS;

            $messages = [
                ['role' => 'system', 'content' => $taskerPlatformContext],
            ];

            if (! empty($context['conversation_id'])) {
                $pastMessages = \App\Models\AIMessage::query()
                    ->where('conversation_id', $context['conversation_id'])
                    ->orderBy('id', 'asc')
                    ->take(10)
                    ->get();

                foreach ($pastMessages as $msg) {
                    $role = $msg->role === 'user' ? 'user' : 'assistant';
                    if (! empty(trim($msg->content))) {
                        $messages[] = [
                            'role' => $role,
                            'content' => $msg->content,
                        ];
                    }
                }
            }

            // Ensure last message is current user prompt
            $lastMsg = end($messages);
            if (! $lastMsg || $lastMsg['role'] !== 'user' || $lastMsg['content'] !== $prompt) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $prompt,
                ];
            }

            $response = Http::timeout(25)
                ->withoutVerifying()
                ->withToken($apiKey)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $text = $response->json('choices.0.message.content');
                if (! empty($text)) {
                    return trim($text);
                }
            }

            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        } catch (Throwable $e) {
            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        }
    }

    /**
     * Analyze workspace using OpenAI / Groq REST API.
     *
     * @return array<string, mixed>
     */
    public function analyzeWorkspace(User $user): array
    {
        return $this->fallbackProvider->analyzeWorkspace($user);
    }
}
