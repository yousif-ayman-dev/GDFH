<?php

namespace App\Services\AI;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Worklog;
use Carbon\Carbon;

class RuleBasedAIProvider implements AIProviderInterface
{
    /**
     * Analyze user workspace and generate health scores, insights, strengths, weaknesses, and recommendations.
     *
     * @return array<string, mixed>
     */
    public function analyzeWorkspace(User $user): array
    {
        $userTeamIds = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->pluck('id');

        $projects = Project::query()
            ->where(function ($q) use ($user, $userTeamIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereIn('team_id', $userTeamIds)
                  ->orWhereHas('memberRecords', fn ($mq) => $mq->where('user_id', $user->id)->where('status', 'active'));
            })
            ->get();

        $projectIds = $projects->pluck('id');
        $tasks = Task::query()->whereIn('project_id', $projectIds)->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn ($t) => in_array($t->status, ['completed', 'done'], true))->count();
        $overdueTasks = $tasks->filter(fn ($t) => $t->due_at && Carbon::parse($t->due_at)->isPast() && ! in_array($t->status, ['completed', 'done'], true))->count();

        $completedThisWeek = $tasks->filter(function ($t) {
            return in_array($t->status, ['completed', 'done'], true) &&
                   $t->updated_at >= now()->startOfWeek();
        })->count();

        $totalTrackedSeconds = (int) Worklog::query()->whereIn('project_id', $projectIds)->sum('duration');
        $totalTrackedHours = round($totalTrackedSeconds / 3600, 1);

        $strengths = [];
        $weaknesses = [];
        $recommendations = [];
        $warnings = [];
        $riskAlerts = [];
        $insights = [];

        // Rule 1: Overdue tasks rule
        if ($overdueTasks > 5) {
            $warnings[] = "يوجد عدد كبير من المهام المتأخرة ({$overdueTasks} مهمة متأخرة).";
            $riskAlerts[] = "خطر تأخير تسليم المخرجات بسبب تراكم {$overdueTasks} مهام متأخرة.";
            $recommendations[] = "إعادة توزيع المهام المتأخرة ذات الأولوية العالية على أعضاء الفريق المتاحين.";
        } elseif ($overdueTasks > 0) {
            $warnings[] = "توجد {$overdueTasks} مهام تجاوزت الموعد النهائي المحدد.";
        } else {
            $strengths[] = "جميع المهام الحالية تسير ضمن جدولها الزمني المخطط بدون أي تأخيرات.";
        }

        // Rule 2: Weekly progress rule
        if ($completedThisWeek === 0 && $totalTasks > 0) {
            $weaknesses[] = "الإنتاجية منخفضة هذا الأسبوع لم يتم إكمال أي مهام حتى الآن.";
            $recommendations[] = "تركيز الجهود على إنهاء المهام المعلقة قيد المراجعة أولاً.";
        } else {
            $insights[] = "تم إنجاز {$completedThisWeek} مهام بنجاح خلال هذا الأسبوع.";
        }

        // Rule 3: Nearly finished projects
        $nearlyDoneProject = $projects->first(fn ($p) => $p->progress() >= 90 && $p->status !== 'completed');
        if ($nearlyDoneProject) {
            $strengths[] = "المشروع '{$nearlyDoneProject->title}' اقترب من الإنجاز بنسبة {$nearlyDoneProject->progress()}%.";
            $recommendations[] = "إجراء المراجعة النهائية للمشروع '{$nearlyDoneProject->title}' لتسليمه رسمياً.";
        }

        // Rule 4: Worklogs & Time tracking rule
        if ($totalTrackedHours > 0) {
            $insights[] = "إجمالي ساعات العمل المسجلة ببيئة العمل بلغ {$totalTrackedHours} ساعة.";
        } else {
            $weaknesses[] = "لم يتم تسجيل أي ساعات عمل عبر نظام تتبع الوقت هذا الأسبوع.";
            $recommendations[] = "تفعيل المؤقت المباشر وتتبع الوقت بدقة للمهام اليومية.";
        }

        // Calculate AI Health Score (0 - 100)
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 100;
        $overduePenalty = min(40, $overdueTasks * 5);
        $healthScore = (int) max(10, min(100, $completionRate - $overduePenalty + ($completedThisWeek > 0 ? 10 : 0)));

        return [
            'health_score' => $healthScore,
            'productivity_insights' => $insights,
            'pending_suggestions' => count($recommendations),
            'risk_alerts' => $riskAlerts,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'recommendations' => $recommendations,
            'warnings' => $warnings,
            'total_projects' => $projects->count(),
            'total_tasks' => $totalTasks,
            'overdue_tasks' => $overdueTasks,
        ];
    }

    /**
     * Generate an AI response for a user prompt using internal rule-based inference.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        $analysis = $this->analyzeWorkspace($user);
        $cleanPrompt = mb_strtolower(trim($prompt));

        if (str_contains($cleanPrompt, 'مشروع') || str_contains($cleanPrompt, 'المشاريع')) {
            return "بناءً على تحليل المشاريع الخاص بك:\n" .
                   "• إجمالي المشاريع النشطة: {$analysis['total_projects']} مشاريع.\n" .
                   (! empty($analysis['strengths']) ? "• " . implode("\n• ", $analysis['strengths']) : "• جميع المشاريع تعمل بشكل مستقر.");
        }

        if (str_contains($cleanPrompt, 'مهام') || str_contains($cleanPrompt, 'تأخير') || str_contains($cleanPrompt, 'متأخر')) {
            return "بناءً على تحليل سريان المهام:\n" .
                   "• إجمالي المهام: {$analysis['total_tasks']} مهمة.\n" .
                   "• المهام المتأخرة: {$analysis['overdue_tasks']} مهام.\n" .
                   (! empty($analysis['warnings']) ? "⚠️ " . implode("\n⚠️ ", $analysis['warnings']) : "✅ جميع المهام منجزة في الموعد المحدد.");
        }

        if (str_contains($cleanPrompt, 'إنتاجية') || str_contains($cleanPrompt, 'أداء') || str_contains($cleanPrompt, 'صحة')) {
            return "مؤشر صحة وبيئة العمل الذكي (AI Health Score) هو {$analysis['health_score']}/100.\n\n" .
                   (! empty($analysis['recommendations']) ? "التوصيات المقترحة لرفع الإنتاجية:\n💡 " . implode("\n💡 ", $analysis['recommendations']) : "أداء بيئة العمل ممتازة ومستقرة تماماً.");
        }

        if (str_contains($cleanPrompt, 'وقت') || str_contains($cleanPrompt, 'ساعات') || str_contains($cleanPrompt, 'تتبع')) {
            return "بناءً على محرك تتبع الوقت:\n" .
                   "• تم رصد السجلات الزمنية للمشاريع والمهام بنجاح.\n" .
                   (! empty($analysis['productivity_insights']) ? "• " . implode("\n• ", $analysis['productivity_insights']) : "• ينصح بالبدء في استخدام المؤقت المباشر لتسجيل الساعات.");
        }

        // Generic intelligent rule-based response
        return "أهلاً بك! أنا مساعد الذكاء الاصطناعي للمؤسسة. بناءً على قراءة بيانات بيئة العمل الخاصة بك:\n\n" .
               "• درجة صحة الأداء الحالية: {$analysis['health_score']}/100\n" .
               "• إجمالي المهام: {$analysis['total_tasks']} | المتأخرة: {$analysis['overdue_tasks']}\n\n" .
               (! empty($analysis['recommendations']) ? "💡 النصيحة الأكثر أهمية لك الآن:\n" . $analysis['recommendations'][0] : "كل الأمور في بيئة العمل تسير بانتظام واحترافية عالية.");
    }
}
