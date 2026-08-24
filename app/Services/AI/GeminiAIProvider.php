<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiAIProvider implements AIProviderInterface
{
    public function __construct(
        protected RuleBasedAIProvider $fallbackProvider
    ) {}

    /**
     * Generate text response using Google Gemini REST API.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (empty($apiKey)) {
            return $this->fallbackProvider->generateResponse($user, $prompt, $context);
        }

        try {
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

                if (! empty($text)) {
                    return trim($text);
                }
            }

            if ($response->status() === 429) {
                return 'تنبيه: تم تجاوز حد الاستخدام المسموح لخدمة الذكاء الاصطناعي (Quota Exceeded). يرجى المحاولة لاحقاً.';
            }

            if ($response->status() === 401 || $response->status() === 403) {
                return 'عذراً، مفتاح الربط مع خدمة الذكاء الاصطناعي (Gemini API Key) غير صالح أو منتهي الصلاحية.';
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
