<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AppNotification;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class DashboardService
{
    /**
     * Get aggregated enterprise dashboard data for a given user.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        // 1. Resolve user accessible projects query
        $userTeamIds = Team::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('memberships', function ($mq) use ($user) {
                      $mq->where('user_id', $user->id)->where('status', 'active');
                  });
            })
            ->pluck('id');

        $projectsQuery = Project::query()->where(function ($q) use ($user, $userTeamIds) {
            $q->where('owner_id', $user->id)
              ->orWhereIn('team_id', $userTeamIds)
              ->orWhereHas('memberRecords', function ($mq) use ($user) {
                  $mq->where('user_id', $user->id)->where('status', 'active');
              });
        });

        $projectIds = (clone $projectsQuery)->pluck('id');

        // 2. Resolve tasks query
        $tasksQuery = Task::query()->whereIn('project_id', $projectIds);

        // 3. Aggregate KPIs
        $totalProjects = (clone $projectsQuery)->count();
        $activeProjectsCount = (clone $projectsQuery)->whereIn('status', ['open', 'in_progress'])->count();
        $completedProjects = (clone $projectsQuery)->where('status', 'completed')->count();

        $openTasksCount = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'done', 'cancelled'])
            ->count();

        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->whereIn('status', ['completed', 'done'])->count();
        $overdueTasksCount = (clone $tasksQuery)
            ->whereNotIn('status', ['completed', 'done', 'cancelled'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $tasksDueToday = (clone $tasksQuery)
            ->whereNotIn('status', ['completed', 'done'])
            ->whereNotNull('due_at')
            ->whereDate('due_at', now()->toDateString())
            ->count();

        $teamsCount = $userTeamIds->count();
        $unreadNotifications = AppNotification::where('user_id', $user->id)->unread()->count();

        // Calculate average project progress
        $activeProjectsList = (clone $projectsQuery)->whereNotIn('status', ['archived'])->get();
        $progressSum = 0;
        foreach ($activeProjectsList as $proj) {
            $progressSum += $proj->progress();
        }
        $overallProgress = $activeProjectsList->count() > 0 ? (int) round($progressSum / $activeProjectsList->count()) : 0;

        // Legacy / Detailed Active Projects collection
        $activeProjectsCollection = (clone $projectsQuery)
            ->whereIn('status', ['open', 'in_progress'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->whereIn('status', ['completed', 'done']);
                },
                'memberRecords as active_members_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->limit(6)
            ->get()
            ->map(function (Project $project) {
                $project->progress_percentage = $project->tasks_count > 0
                    ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
                    : 0;

                return $project;
            });

        $upcomingTasks = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'done', 'cancelled'])
            ->with('project:id,title,slug')
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->limit(6)
            ->get();

        $projectDeadlines = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', today())
            ->orderBy('deadline')
            ->limit(4)
            ->get();

        $teamsCollection = Team::query()
            ->whereIn('id', $userTeamIds)
            ->withCount([
                'memberships as active_members_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->latest()
            ->limit(6)
            ->get();

        // 4. Analytics Widgets
        $projectCompletionRate = $totalProjects > 0 ? (int) round(($completedProjects / $totalProjects) * 100) : 0;
        $taskCompletionRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $recentActivitiesCount = Activity::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($q) use ($user, $projectIds) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($sq) use ($projectIds) {
                      $sq->where('subject_type', Project::class)->whereIn('subject_id', $projectIds);
                  });
            })
            ->count();

        $teamsSummary = Team::query()
            ->whereIn('id', $userTeamIds)
            ->withCount(['memberships as members_count', 'projects as projects_count'])
            ->take(5)
            ->get();

        // 5. Eager-loaded Recents
        $recentProjects = (clone $projectsQuery)
            ->with(['owner', 'team'])
            ->latest()
            ->take(5)
            ->get();

        $recentTasks = (clone $tasksQuery)
            ->with(['project', 'assignee', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $recentComments = Comment::query()
            ->whereHasMorph('commentable', [Project::class, Task::class], function ($q, $type) use ($projectIds) {
                if ($type === Project::class) {
                    $q->whereIn('id', $projectIds);
                } else {
                    $q->whereIn('project_id', $projectIds);
                }
            })
            ->with(['user', 'commentable'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = Activity::query()
            ->where(function ($q) use ($user, $projectIds) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($sq) use ($projectIds) {
                      $sq->where('subject_type', Project::class)->whereIn('subject_id', $projectIds);
                  });
            })
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentAttachments = Attachment::query()
            ->whereHasMorph('attachable', [Project::class, Task::class], function ($q, $type) use ($projectIds) {
                if ($type === Project::class) {
                    $q->whereIn('id', $projectIds);
                } else {
                    $q->whereIn('project_id', $projectIds);
                }
            })
            ->with(['user', 'attachable'])
            ->latest()
            ->take(5)
            ->get();

        $recentNotifications = AppNotification::query()
            ->where('user_id', $user->id)
            ->with('sender')
            ->latest()
            ->take(5)
            ->get();

        $statsOverdueTasksCount = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'done', 'cancelled'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        return [
            'stats' => [
                'active_projects' => $activeProjectsCount,
                'open_tasks' => $openTasksCount,
                'teams' => $teamsCount,
                'overdue_tasks' => $statsOverdueTasksCount,
            ],
            'activeProjects' => $activeProjectsCollection,
            'upcomingTasks' => $upcomingTasks,
            'projectDeadlines' => $projectDeadlines,
            'teams' => $teamsCollection,

            'kpis' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjectsCount,
                'completed_projects' => $completedProjects,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasksCount,
                'tasks_due_today' => $tasksDueToday,
                'teams_count' => $teamsCount,
                'unread_notifications' => $unreadNotifications,
                'overall_progress' => $overallProgress,
            ],
            'analytics' => [
                'project_completion_rate' => $projectCompletionRate,
                'task_completion_rate' => $taskCompletionRate,
                'recent_activities_count' => $recentActivitiesCount,
                'teams_summary' => $teamsSummary,
            ],
            'recents' => [
                'projects' => $recentProjects,
                'tasks' => $recentTasks,
                'comments' => $recentComments,
                'activities' => $recentActivities,
                'attachments' => $recentAttachments,
                'notifications' => $recentNotifications,
            ],
        ];
    }
}
