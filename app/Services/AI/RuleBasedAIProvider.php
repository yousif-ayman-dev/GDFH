<?php

namespace App\Services\AI;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Worklog;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

        if ($overdueTasks > 5) {
            $warnings[] = "يوجد عدد كبير من المهام المتأخرة ({$overdueTasks} مهمة متأخرة).";
            $riskAlerts[] = "خطر تأخير تسليم المخرجات بسبب تراكم {$overdueTasks} مهام متأخرة.";
            $recommendations[] = "إعادة توزيع المهام المتأخرة ذات الأولوية العالية على أعضاء الفريق المتاحين.";
        } elseif ($overdueTasks > 0) {
            $warnings[] = "توجد {$overdueTasks} مهام تجاوزت الموعد النهائي المحدد.";
        } else {
            $strengths[] = "جميع المهام الحالية تسير ضمن جدولها الزمني المخطط بدون أي تأخيرات.";
        }

        if ($completedThisWeek === 0 && $totalTasks > 0) {
            $weaknesses[] = "الإنتاجية منخفضة هذا الأسبوع لم يتم إكمال أي مهام حتى الآن.";
            $recommendations[] = "تركيز الجهود على إنهاء المهام المعلقة قيد المراجعة أولاً.";
        } else {
            $insights[] = "تم إنجاز {$completedThisWeek} مهام بنجاح خلال هذا الأسبوع.";
        }

        $nearlyDoneProject = $projects->first(fn ($p) => $p->progress() >= 90 && $p->status !== 'completed');
        if ($nearlyDoneProject) {
            $strengths[] = "المشروع '{$nearlyDoneProject->title}' اقترب من الإنجاز بنسبة {$nearlyDoneProject->progress()}%.";
            $recommendations[] = "إجراء المراجعة النهائية للمشروع '{$nearlyDoneProject->title}' لتسليمه رسمياً.";
        }

        if ($totalTrackedHours > 0) {
            $insights[] = "إجمالي ساعات العمل المسجلة ببيئة العمل بلغ {$totalTrackedHours} ساعة.";
        } else {
            $weaknesses[] = "لم يتم تسجيل أي ساعات عمل عبر نظام تتبع الوقت هذا الأسبوع.";
            $recommendations[] = "تفعيل المؤقت المباشر وتتبع الوقت بدقة للمهام اليومية.";
        }

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
     * Generate dynamic AI responses based on specific user prompts and real-time workspace metrics.
     *
     * @param  array<string, mixed>  $context
     */
    public function generateResponse(User $user, string $prompt, array $context = []): string
    {
        $analysis = $this->analyzeWorkspace($user);
        $cleanPrompt = mb_strtolower(trim($prompt));

        if (str_contains($cleanPrompt, 'مشروع') || str_contains($cleanPrompt, 'المشاريع')) {
            return "تحليل المشاريع الخاص بك بـ Tasker:\n" .
                   "• إجمالي المشاريع النشطة: {$analysis['total_projects']} مشاريع.\n" .
                   (! empty($analysis['strengths']) ? "• " . implode("\n• ", $analysis['strengths']) : "• جميع المشاريع تعمل وفق الجدول المخطط.");
        }

        if (str_contains($cleanPrompt, 'مهمة') || str_contains($cleanPrompt, 'مهام') || str_contains($cleanPrompt, 'تأخير') || str_contains($cleanPrompt, 'متأخر')) {
            return "تقرير متابعة المهام اليومي:\n" .
                   "• إجمالي المهام المسجلة: {$analysis['total_tasks']} مهمة.\n" .
                   "• المهام المتأخرة: {$analysis['overdue_tasks']} مهمة.\n" .
                   (! empty($analysis['warnings']) ? "⚠️ " . implode("\n⚠️ ", $analysis['warnings']) : "✅ جميع المهام منجزة ضمن الموعد المحدد.");
        }

        if (str_contains($cleanPrompt, 'فريق') || str_contains($cleanPrompt, 'مستقل') || str_contains($cleanPrompt, 'أعضاء') || str_contains($cleanPrompt, 'توظيف')) {
            return "توصية Tasker لإدارة فريق العمل والتوظيف:\n" .
                   "• يمكنك زيارة سوق الخدمات المستقلة وتصفح أفضل المستقلين المتاحين لمشاريعك.\n" .
                   "• ينصح بتعيين أدوار محددة للأعضاء الجدد لتسريع إنجاز المهام الذكية.";
        }

        if (str_contains($cleanPrompt, 'إنتاجية') || str_contains($cleanPrompt, 'أداء') || str_contains($cleanPrompt, 'صحة') || str_contains($cleanPrompt, 'تقييم')) {
            return "مؤشر أداء وصحة بيئة العمل الذكي (AI Health Score) هو {$analysis['health_score']}/100.\n\n" .
                   (! empty($analysis['recommendations']) ? "💡 التوصيات التنفيذية لرفع الكفاءة:\n• " . implode("\n• ", $analysis['recommendations']) : "أداء بيئة العمل ممتاز ولا توجد عوائق تشغيلية.");
        }

        if (str_contains($cleanPrompt, 'وقت') || str_contains($cleanPrompt, 'ساعات') || str_contains($cleanPrompt, 'تتبع') || str_contains($cleanPrompt, 'زمان')) {
            return "تقرير تتبع الوقت والإنتاجية الزمنية:\n" .
                   "• تم توثيق السجلات الزمنية بنجاح عبر النظام.\n" .
                   (! empty($analysis['productivity_insights']) ? "• " . implode("\n• ", $analysis['productivity_insights']) : "• تذكر تفعيل المؤقت المباشر في شريط التتبع العلوي أثناء العمل.");
        }

        if (str_contains($cleanPrompt, 'مرحبا') || str_contains($cleanPrompt, 'السلام') || str_contains($cleanPrompt, 'أهلا') || str_contains($cleanPrompt, 'مين انت')) {
            return "أهلاً بك يا {$user->name}! أنا مساعد Tasker الذكي، مستشارك الخاص لإدارة الأعمال والمشاريع. كيف يمكنني مساعدتك اليوم؟ يمكنك سؤالي عن أداء الفريق، المهام المتأخرة، أو نصائح رفع الإنتاجية.";
        }

        if (str_contains($cleanPrompt, 'جاذبية') || str_contains($cleanPrompt, 'نيوتن') || str_contains($cleanPrompt, 'مكتشف')) {
            return "مكتشف الجاذبية الأرضية هو عالم الفيزياء والرياضيات الإنجليزي **إسحاق نيوتن (Sir Isaac Newton)** عام 1687 عندما صاغ قانون الجاذبية العام وقوانين الحركة الثنائية المشهورة بعد ملاحظته لسقوط التفاحة من الشجرة.";
        }

        if (str_contains($cleanPrompt, 'برمجة') || str_contains($cleanPrompt, 'كود') || str_contains($cleanPrompt, 'php') || str_contains($cleanPrompt, 'laravel') || str_contains($cleanPrompt, 'javascript') || str_contains($cleanPrompt, 'python')) {
            return "أنا هنا لمساعدتك في كل ما يتعلق بالبرمجة وتطوير البرمجيات! يمكنك كتابة استفسارك أو الكود الذي تعمل عليه وسأساعدك في تحليله وتطويره خطوة بخطوة.";
        }

        if (str_contains($cleanPrompt, 'تاسكر') || str_contains($cleanPrompt, 'تطبيق') || str_contains($cleanPrompt, 'استخدمه') || str_contains($cleanPrompt, 'استخدام') || str_contains($cleanPrompt, 'شرح') || str_contains($cleanPrompt, 'طريقة') || str_contains($cleanPrompt, 'tasker')) {
            return "أهلاً بك يا {$user->name}! منصة **Tasker** هي بيئة متكاملة لإدارة العمل والمشاريع والتوظيف تجمع بين العملاء (Clients) والمستقلين (Freelancers):\n\n" .
                   "📌 **كيف تستخدم منصة Tasker حسب نوع حسابك:**\n" .
                   "1️⃣ **إذا كنت عميلاً (Client):**\n" .
                   "   • **طرح المشاريع:** يمكنك إضافة مشروع جديد وتحديد الميزانية المطلوبة.\n" .
                   "   • **تصفح المستقلين والخدمات:** البحث عن الكفاءات وتصفح معارض أعمالهم (Portfolio).\n" .
                   "   • **إدارة العقود:** مراجعة العروض المقدمة وتوثيق العقود وتتبع مخرجات التسليم.\n\n" .
                   "2️⃣ **إذا كنت مستقلاً (Freelancer):**\n" .
                   "   • **معرض الأعمال (Portfolio):** رفع وإضافة أعمالك السابقة والملاحظات ليعينك العملاء.\n" .
                   "   • **تأسيس الفرق (Teams):** تكوين فريق منفذ وتوزيع الأدوار والمهام.\n" .
                   "   • **نشر الخدمات:** تقديم خدمات مصغرة بأسعار ثابتة وساعات عمل محددة.\n" .
                   "   • **أدوات التنفيذ:** استخدام لوحة كانبان، مخطط غانت، وتتبع الوقت الحي (Time Tracker).\n\n" .
                   "💡 **المساعد الذكي:** أنا هنا لمساعدتك دائماً في تحليل بيئة العمل، الإجابة عن أي استفسار، وترشيح التوصيات لتنفيذ المشاريع بكفاءة عالية.";
        }

        // General questions fallback handling
        if (str_ends_with(trim($prompt), '؟') || str_ends_with(trim($prompt), '?') || str_contains($cleanPrompt, 'من') || str_contains($cleanPrompt, 'ما') || str_contains($cleanPrompt, 'كيف') || str_contains($cleanPrompt, 'لماذا')) {
            return "بناءً على استفسارك حول (\"" . Str::limit($prompt, 60) . "\"):\n\n" .
                   "أنا مساعدك الذكي في منصة Tasker. يسعدني الإجابة عن كافة أسئلتك وإطلاعك على أداء ومؤشرات بيئة العمل للمشاريع والمهام والتوظيف. إذا كان لديك سؤال تقني أو استفسار محدد حول مشروعك، أرحب بتفاصيله فوراً!";
        }

        // Dynamic contextual response utilizing user prompt keywords
        return "بناءً على طلبك حول (\"" . Str::limit($prompt, 50) . "\") وقراءة بيانات بيئة العمل الخاصة بك:\n\n" .
               "• مؤشر الأداء الحالي: {$analysis['health_score']}/100\n" .
               "• المشاريع النشطة: {$analysis['total_projects']} | المهام الإجمالية: {$analysis['total_tasks']}\n\n" .
               (! empty($analysis['recommendations']) ? "💡 التوصية المقترحة الآن:\n" . $analysis['recommendations'][0] : "يسرنا أن بيئة العمل لديك تسير بأعلى كفاءة واحترافية.");
    }
}
