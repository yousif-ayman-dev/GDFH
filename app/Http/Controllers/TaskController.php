<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function all(Request $request): View
    {
        $user = Auth::user();

        $userTeamIds = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->pluck('id');

        $projectIds = Project::query()
            ->where(function ($q) use ($user, $userTeamIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereIn('team_id', $userTeamIds)
                  ->orWhereHas('memberRecords', fn ($mq) => $mq->where('user_id', $user->id)->where('status', 'active'));
            })
            ->pluck('id');

        $filters = [
            'search' => $request->query('search'),
            'project_id' => $request->query('project_id'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'overdue' => $request->boolean('overdue'),
        ];

        $query = Task::query()
            ->whereIn('project_id', $projectIds)
            ->with(['project', 'assignee', 'creator']);

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->whereIn('status', ['completed', 'done']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['overdue'])) {
            $query->whereNotIn('status', ['completed', 'done', 'cancelled'])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now());
        }

        $tasks = $query->latest('updated_at')->paginate(12)->withQueryString();
        $userProjects = Project::query()->whereIn('id', $projectIds)->get(['id', 'title']);

        return view('tasks.global_index', compact('tasks', 'filters', 'userProjects'));
    }

    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->latest()
            ->paginate(10);

        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project): View
    {
        $this->authorize('create', [Task::class, $project]);

        return view('tasks.create', compact('project'));
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $data = $request->validated();
        $task = $this->taskService->createTask(Auth::user(), $project, $data);

        return redirect()
            ->route('projects.tasks.show', [$project, $task])
            ->with('success', 'تم إنشاء المهمة بنجاح.');
    }

    public function show(Project $project, Task $task): View
    {
        $this->ensureTaskBelongsToProject($project, $task);
        $this->authorize('view', $task);

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task): View
    {
        $this->ensureTaskBelongsToProject($project, $task);
        $this->authorize('update', $task);

        return view('tasks.edit', compact('project', 'task'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->ensureTaskBelongsToProject($project, $task);
        $this->authorize('update', $task);

        $this->taskService->updateTask($task, $request->validated());

        return redirect()
            ->route('projects.tasks.show', [$project, $task])
            ->with('success', 'تم تحديث المهمة بنجاح.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->ensureTaskBelongsToProject($project, $task);
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', 'تم حذف المهمة بنجاح.');
    }

    private function ensureTaskBelongsToProject(Project $project, Task $task): void
    {
        abort_unless($task->project_id === $project->id, 404);
    }
}
