<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiAIProvider implements AIProviderInterface
{
    public function __construct(
        protected RuleBasedAIProvider $fallbackProvider,
        protected ?AISanitizer $sanitizer = null
    ) {
        $this->sanitizer = $sanitizer ?? new AISanitizer();
    }

    /**
     * Generate text response using Google Gemini REST API.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        $prompt = $this->sanitizer->sanitize($prompt);
        $context = $this->sanitizer->sanitizeContext($context);

        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        }

        try {
            $analysis = $this->fallbackProvider->analyzeWorkspace($user);
            $roleLabel = $user->isClient() ? 'عميل (Client - صاحب عمل)' : 'مستقل (Freelancer - منفذ ومبدع)';

            $taskerPlatformContext = <<<SYS
أنت مساعد الذكاء الاصطناعي المتقدم والتفاعلي لمنصة "Tasker" (Tasker AI Bot).
المستخدم الحالي: "{$user->name}" — نوع الحساب: [{$roleLabel}].

بيانات وتحليلات بيئة العمل الحية الخاصة بـ {$user->name} حالياً في النظام:
• إجمالي المشاريع: {$analysis['total_projects']} مشروع
• إجمالي المهام: {$analysis['total_tasks']} مهمة
• المهام المتأخرة: {$analysis['overdue_tasks']} مهمة
• مؤشر صحة الأداء: {$analysis['health_score']}/100

تعليمات الإجابة والتفاعل:
1. أنت تعمل تماماً مثل ChatGPT و Google Gemini: أجب عن أي سؤال يطرحه المستخدم بذكاء وطلاقة وثقافة عالية في كافة المجالات (برمجة، علوم، تاريخ، كتابة محتوى، نصائح عامة، تخطيط).
2. عندما يسألك المستخدم عن مشاريعه أو مهامه أو منصة Tasker، استعن بأرقام وبيانات بيئة العمل المذكورة أعلاه لإعطائه إجابات وتحليلات حقيقية ومباشرة.
SYS;

            $systemInstruction = [
                'parts' => [
                    ['text' => $taskerPlatformContext]
                ]
            ];

            // Build contents payload with conversation history if available
            $contents = [];
            $lastRole = null;

            if (! empty($context['conversation_id'])) {
                $pastMessages = \App\Models\AIMessage::query()
                    ->where('conversation_id', $context['conversation_id'])
                    ->orderBy('id', 'asc')
                    ->take(10)
                    ->get();

                foreach ($pastMessages as $msg) {
                    $role = $msg->role === 'user' ? 'user' : 'model';
                    // Strict alternating roles check for Gemini REST API
                    if ($role !== $lastRole && ! empty(trim($msg->content))) {
                        $contents[] = [
                            'role' => $role,
                            'parts' => [
                                ['text' => $msg->content],
                            ],
                        ];
                        $lastRole = $role;
                    }
                }
            }

            // If last item in history is 'user', remove it so we append the fresh prompt cleanly
            if (! empty($contents) && end($contents)['role'] === 'user') {
                array_pop($contents);
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ];

            $payload = [
                'system_instruction' => $systemInstruction,
                'contents' => $contents,
            ];

            $modelsToTry = array_unique([$model, 'gemini-3.5-flash', 'gemini-2.5-flash', 'gemini-flash-latest']);

            foreach ($modelsToTry as $currentModel) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$apiKey}";

                $response = Http::timeout(20)
                    ->withoutVerifying()
                    ->post($url, $payload);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');

                    if (! empty($text)) {
                        return trim($text);
                    }
                }

                // Fallback to single prompt payload if history payload fails
                $simplePayload = [
                    'system_instruction' => $systemInstruction,
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ];

                $simpleResponse = Http::timeout(20)->withoutVerifying()->post($url, $simplePayload);
                if ($simpleResponse->successful()) {
                    $text = $simpleResponse->json('candidates.0.content.parts.0.text');
                    if (! empty($text)) {
                        return trim($text);
                    }
                }
            }

            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        } catch (Throwable $e) {
            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        }
    }

    /**
     * Analyze user workspace performance metrics using Google Gemini REST API.
     *
     * @return array<string, mixed>
     */
    public function analyzeWorkspace(User $user): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (empty($apiKey)) {
            return $this->fallbackProvider->analyzeWorkspace($user);
        }

        try {
            $fallbackAnalysis = $this->fallbackProvider->analyzeWorkspace($user);
            $prompt = $this->buildWorkspacePrompt($user, $fallbackAnalysis);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(30)
                ->retry(2, 100)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');

                if ($text) {
                    $jsonStart = strpos($text, '{');
                    $jsonEnd = strrpos($text, '}');

                    if ($jsonStart !== false && $jsonEnd !== false) {
                        $jsonStr = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
                        $data = json_decode($jsonStr, true);

                        if (is_array($data) && isset($data['health_score'])) {
                            return [
                                'health_score' => (int) max(10, min(100, $data['health_score'])),
                                'summary' => (string) ($data['summary'] ?? ''),
                                'strengths' => (array) ($data['strengths'] ?? []),
                                'weaknesses' => (array) ($data['weaknesses'] ?? []),
                                'risks' => (array) ($data['risks'] ?? []),
                                'recommendations' => (array) ($data['recommendations'] ?? []),
                                'warnings' => (array) ($data['warnings'] ?? $fallbackAnalysis['warnings'] ?? []),
                                'total_projects' => $fallbackAnalysis['total_projects'] ?? 0,
                                'total_tasks' => $fallbackAnalysis['total_tasks'] ?? 0,
                                'overdue_tasks' => $fallbackAnalysis['overdue_tasks'] ?? 0,
                            ];
                        }
                    }
                }
            }

            return $fallbackAnalysis;
        } catch (Throwable $e) {
            return $this->fallbackProvider->analyzeWorkspace($user);
        }
    }

    /**
     * Build structured prompt for workspace analysis.
     *
     * @param  array<string, mixed>  $fallbackAnalysis
     */
    protected function buildWorkspacePrompt(User $user, array $fallbackAnalysis): string
    {
        $totalProjects = $fallbackAnalysis['total_projects'] ?? 0;
        $totalTasks = $fallbackAnalysis['total_tasks'] ?? 0;
        $overdueTasks = $fallbackAnalysis['overdue_tasks'] ?? 0;
        $healthScore = $fallbackAnalysis['health_score'] ?? 80;

        return <<<PROMPT
قم بتحليل بيانات بيئة العمل للمستخدم {$user->name} بلغة عربية احترافية ودقيقة لمنصة Tasker:
- إجمالي المشاريع: {$totalProjects}
- إجمالي المهام: {$totalTasks}
- المهام المتأخرة: {$overdueTasks}
- مؤشر الصحة المحسوب: {$healthScore}/100

أرجع الإجابة فقط بتنسيق JSON صالح يحتوي على الحقول التالية:
{
  "health_score": 85,
  "summary": "ملخص عام رائع عن حالة بيئة العمل",
  "strengths": ["نقطة قوة 1", "نقطة قوة 2"],
  "weaknesses": ["نقطة تحسين 1"],
  "risks": ["مخاطرة محتملة 1"],
  "recommendations": ["توصية عملية 1", "توصية عملية 2"],
  "warnings": ["تحذير هام 1"]
}
PROMPT;
    }
}
