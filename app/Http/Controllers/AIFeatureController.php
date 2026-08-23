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
}
