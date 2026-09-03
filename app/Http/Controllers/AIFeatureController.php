<?php

namespace App\Http\Controllers;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIFeatureController extends Controller
{
    public function __construct(
        protected AIProviderInterface $aiProvider
    ) {}

    /**
     * Requirement #15 — Analyze project description and suggest required specializations.
     * POST /ai/analyze-project
     */
    public function analyzeProject(Request $request): JsonResponse
    {
        $request->validate([
            'description' => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        $description = $request->input('description');
        $user = Auth::user();

        $prompt = <<<PROMPT
أنت مساعد ذكي متخصص في مشاريع البرمجيات والعمل الحر. المستخدم يريد إنشاء مشروع وأعطاك وصفه:

"{$description}"

المطلوب بدقة:
1. حدد التخصصات والمهارات التقنية المطلوبة لتنفيذ هذا المشروع (مثال: Laravel، React، Python، UX Design...).
2. اقترح التخصصات الوظيفية لأعضاء الفريق المناسبين (مثال: مطور باك-إند، مصمم واجهات...).
3. قدّر المدة الزمنية التقريبية للمشروع.

أعد الرد باللغة العربية بتنسيق واضح ومنظم دون مقدمات غير ضرورية.
PROMPT;

        $aiResponse = $this->aiProvider->generateResponse($user, $prompt, [
            'context' => 'project_analysis',
            'description' => $description,
        ]);

        return response()->json([
            'success' => true,
            'suggestions' => $aiResponse,
        ]);
    }

    /**
     * Requirement #16 — Suggest team members based on skills and ratings.
     * POST /ai/suggest-members
     */
    public function suggestMembers(Request $request): JsonResponse
    {
        $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $user = Auth::user();
        $teamId = $request->input('team_id');

        /** @var Team $team */
        $team = Team::with(['memberships.user'])->findOrFail($teamId);

        // Authorization: user must be a member or owner of this team
        $isMember = $team->owner_id === $user->id
            || $team->memberships->contains(fn ($m) => $m->user_id === $user->id && $m->status === 'active');

        if (! $isMember) {
            return response()->json(['success' => false, 'message' => 'غير مصرح.'], 403);
        }

        // Get current member skills to understand team composition
        $currentMemberIds = $team->memberships->pluck('user_id')->push($team->owner_id)->unique()->toArray();
        $currentMembersNames = $team->memberships->map(fn ($m) => $m->user?->name)->filter()->join(', ');

        // Get top-rated available freelancers not already in team
        $candidates = FreelancerProfile::with('user')
            ->where('availability', 'available')
            ->whereNotIn('user_id', $currentMemberIds)
            ->orderByDesc('rating')
            ->orderByDesc('completed_projects_count')
            ->limit(10)
            ->get();

        if ($candidates->isEmpty()) {
            return response()->json([
                'success' => true,
                'suggestions' => [
                    'text' => 'لا يوجد مستقلون متاحون حالياً في المنصة.',
                    'freelancers' => [],
                ],
            ]);
        }

        $candidatesInfo = $candidates->map(fn ($fp) => [
            'name' => $fp->user?->name,
            'title' => $fp->title,
            'skills' => implode(', ', $fp->skills ?? []),
            'rating' => $fp->rating,
            'completed_projects' => $fp->completed_projects_count,
        ])->toArray();

        $candidatesText = collect($candidatesInfo)->map(function ($c) {
            return "- {$c['name']} | {$c['title']} | المهارات: {$c['skills']} | التقييم: {$c['rating']}/5 | المشاريع المنجزة: {$c['completed_projects']}";
        })->join("\n");

        $teamName = $team->name;
        $prompt = <<<PROMPT
أنت مساعد ذكي لإدارة الفرق. فريق "{$teamName}" لديه الأعضاء الحاليون: {$currentMembersNames}.

قائمة المستقلين المتاحين على المنصة:
{$candidatesText}

المطلوب: اقترح أفضل 3 مستقلين من القائمة للانضمام لهذا الفريق مع تبرير كل اقتراح بشكل موجز باللغة العربية.
PROMPT;

        $aiText = $this->aiProvider->generateResponse($user, $prompt, ['context' => 'team_member_suggestion']);

        // Return both AI text + structured freelancer data for display
        $top3 = $candidates->take(3)->map(fn ($fp) => [
            'id'                 => $fp->user_id,
            'name'               => $fp->user?->name,
            'title'              => $fp->title,
            'skills'             => $fp->skills ?? [],
            'rating'             => (float) $fp->rating,
            'completed_projects' => $fp->completed_projects_count,
            'profile_url'        => route('marketplace.freelancers.show', $fp->user_id),
        ])->values();

        return response()->json([
            'success'     => true,
            'suggestions' => [
                'text'        => $aiText,
                'freelancers' => $top3,
            ],
        ]);
    }

    /**
     * Requirement #17 — Recommend open projects for the logged-in freelancer.
     * GET /ai/recommended-projects
     */
    public function recommendedProjects(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Only relevant for freelancers with a profile
        if (! $user->isFreelancer()) {
            return response()->json(['success' => true, 'projects' => [], 'message' => 'هذه الميزة للمستقلين فقط.']);
        }

        $profile = $user->freelancerProfile;
        $skills = $profile?->skills ?? [];

        // Fetch open public projects
        $openProjects = Project::query()
            ->where('visibility', 'public')
            ->whereIn('status', ['draft', 'in_progress'])
            ->where('owner_id', '!=', $user->id)
            ->with('owner')
            ->latest()
            ->limit(50)
            ->get();

        if ($openProjects->isEmpty()) {
            return response()->json(['success' => true, 'projects' => [], 'message' => 'لا توجد مشاريع مفتوحة حالياً.']);
        }

        // Simple scoring: match skills keywords against project title + category + description
        $skillsLower = array_map('mb_strtolower', $skills);

        $scored = $openProjects->map(function ($project) use ($skillsLower) {
            $haystack = mb_strtolower(
                ($project->title ?? '') . ' ' .
                ($project->description ?? '') . ' ' .
                ($project->category ?? '')
            );

            $score = 0;
            foreach ($skillsLower as $skill) {
                if (str_contains($haystack, $skill)) {
                    $score++;
                }
            }

            return ['project' => $project, 'score' => $score];
        })->filter(fn ($item) => $item['score'] > 0 || count($skillsLower) === 0)
          ->sortByDesc('score')
          ->take(5);

        // If no skill matches, just return latest 5
        if ($scored->isEmpty()) {
            $scored = $openProjects->take(5)->map(fn ($p) => ['project' => $p, 'score' => 0]);
        }

        $result = $scored->map(fn ($item) => [
            'id'          => $item['project']->id,
            'title'       => $item['project']->title,
            'description' => \Str::limit($item['project']->description ?? '', 100),
            'category'    => $item['project']->category,
            'owner'       => $item['project']->owner?->name,
            'url'         => route('projects.show', $item['project']),
            'match_score' => $item['score'],
        ])->values();

        return response()->json([
            'success'  => true,
            'projects' => $result,
        ]);
    }

    /**
     * Requirement / AI Feature — Automatically generate task breakdown for a project.
     * POST /projects/{project}/ai-generate-tasks
     */
    public function generateTaskBreakdown(Request $request, Project $project): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        $canManage = $user->isAdmin()
            || $user->id === $project->owner_id
            || $project->members()->where('user_id', $user->id)->exists();

        if (! $canManage) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بتوليد المهام لهذا المشروع.'], 403);
            }
            return redirect()->back()->with('error', 'غير مصرح لك بتوليد المهام لهذا المشروع.');
        }

        $prompt = <<<PROMPT
أنت مهندس مدير مشاريع خبير. قم بتفكيك هذا المشروع إلى 4 إلى 6 مهام عملية، واضحة ومحددة.

عنوان المشروع: {$project->title}
وصف المشروع: {$project->description}

أعد الرد بصيغة JSON فقط مصفوفة من الكائنات بالشكل التالي دون أي نص إضافي:
[
  {
    "title": "عنوان المهمة",
    "description": "وصف قصير للمهمة",
    "priority": "medium",
    "estimated_minutes": 120
  }
]
PROMPT;

        $aiText = $this->aiProvider->generateResponse($user, $prompt, [
            'context' => 'task_breakdown',
            'project_id' => $project->id,
        ]);

        // Attempt to parse JSON response
        $tasksData = null;
        if (preg_match('/\[.*\]/s', $aiText, $matches)) {
            $tasksData = json_decode($matches[0], true);
        }

        if (! is_array($tasksData) || empty($tasksData)) {
            $tasksData = [
                [
                    'title' => 'تحليل المتطلبات وتجهيز الخطة التكتيكية',
                    'description' => 'دراسة وصف المشروع وتحديد المهام التقنية وتوزيع الأدوار.',
                    'priority' => 'high',
                    'estimated_minutes' => 180,
                ],
                [
                    'title' => 'تصميم الهيكلية والنموذج الأولي للواجهات',
                    'description' => 'تجهيز وتصميم مخططات وتجربة المستخدم الرئيسية للمشروع.',
                    'priority' => 'medium',
                    'estimated_minutes' => 360,
                ],
                [
                    'title' => 'تطوير البرمجيات والربط البرمجي',
                    'description' => 'تنفيذ الأكواد الرئيسية والمنطق العملياتي وقواعد البيانات.',
                    'priority' => 'urgent',
                    'estimated_minutes' => 720,
                ],
                [
                    'title' => 'الاختبار والمراجعة وضمان الجودة',
                    'description' => 'فحص الأخطاء والتأكد من توافق الميزات وتكامل النظام.',
                    'priority' => 'medium',
                    'estimated_minutes' => 240,
                ],
            ];
        }

        $createdTasks = [];
        $sortOrder = (int) ($project->tasks()->max('sort_order') ?? 0);

        foreach ($tasksData as $data) {
            $sortOrder += 10;
            $created = $project->tasks()->create([
                'title' => $data['title'] ?? 'مهمة جديدة',
                'description' => $data['description'] ?? null,
                'priority' => in_array($data['priority'] ?? 'medium', ['low', 'medium', 'high', 'urgent'], true) ? $data['priority'] : 'medium',
                'status' => 'todo',
                'created_by' => $user->id,
                'team_id' => $project->team_id,
                'estimated_minutes' => (int) ($data['estimated_minutes'] ?? 120),
                'sort_order' => $sortOrder,
                'due_at' => now()->addDays(count($createdTasks) + 2),
            ]);
            $createdTasks[] = $created;
        }

        $message = 'تم إنشاء ' . count($createdTasks) . ' مهام تلقائياً بنجاح بواسطة الذكاء الاصطناعي ✨';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'tasks' => $createdTasks,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}


