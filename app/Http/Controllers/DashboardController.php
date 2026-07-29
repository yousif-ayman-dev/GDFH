<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Projects accessible to the user
        |--------------------------------------------------------------------------
        |
        | A project is part of the user's workspace when they own it or have an
        | active membership in it.
        |
        */

        $projectsQuery = Project::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereHas('memberRecords', function ($membershipQuery) use ($user) {
                        $membershipQuery
                            ->where('user_id', $user->id)
                            ->where('status', 'active');
                    });
            });

        $projectIds = (clone $projectsQuery)->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */

        $activeProjectsCount = (clone $projectsQuery)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $openTasksCount = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $overdueTasksCount = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $teamsCount = Team::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereHas('memberships', function ($membershipQuery) use ($user) {
                        $membershipQuery
                            ->where('user_id', $user->id)
                            ->where('status', 'active');
                    });
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Active projects
        |--------------------------------------------------------------------------
        */

        $activeProjects = (clone $projectsQuery)
            ->whereIn('status', ['open', 'in_progress'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', 'completed');
                },
                'memberRecords as active_members_count' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->limit(4)
            ->get()
            ->map(function (Project $project) {
                $project->progress_percentage = $project->tasks_count > 0
                    ? (int) round(
                        ($project->completed_tasks_count / $project->tasks_count) * 100
                    )
                    : 0;

                return $project;
            });

        /*
        |--------------------------------------------------------------------------
        | Assigned tasks
        |--------------------------------------------------------------------------
        */

        $upcomingTasks = Task::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('project:id,title,slug')
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Upcoming deadlines
        |--------------------------------------------------------------------------
        */

        $projectDeadlines = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', today())
            ->orderBy('deadline')
            ->limit(4)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | User teams
        |--------------------------------------------------------------------------
        */

        $teams = Team::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereHas('memberships', function ($membershipQuery) use ($user) {
                        $membershipQuery
                            ->where('user_id', $user->id)
                            ->where('status', 'active');
                    });
            })
            ->withCount([
                'memberships as active_members_count' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->latest()
            ->limit(4)
            ->get();

        return view('dashboard', [
            'stats' => [
                'active_projects' => $activeProjectsCount,
                'open_tasks' => $openTasksCount,
                'teams' => $teamsCount,
                'overdue_tasks' => $overdueTasksCount,
            ],

            'activeProjects' => $activeProjects,
            'upcomingTasks' => $upcomingTasks,
            'projectDeadlines' => $projectDeadlines,
            'teams' => $teams,
        ]);
    }
}
