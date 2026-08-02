<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

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
