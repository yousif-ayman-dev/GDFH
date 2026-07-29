<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Project $project): View
    {
        $this->ensureProjectOwner($project);

        $tasks = $project->tasks()
            ->latest()
            ->paginate(10);

        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project): View
    {
        $this->ensureProjectOwner($project);

        return view('tasks.create', compact('project'));
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validated();

        $task = Task::create([
            ...$data,
            'project_id' => $project->id,
            'created_by' => Auth::id(),
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
        ]);

        return redirect()
            ->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task created successfully.');
    }

    public function show(Project $project, Task $task): View
    {
        $this->ensureProjectOwner($project);
        $this->ensureTaskBelongsToProject($project, $task);

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task): View
    {
        $this->ensureProjectOwner($project);
        $this->ensureTaskBelongsToProject($project, $task);

        return view('tasks.edit', compact('project', 'task'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensureTaskBelongsToProject($project, $task);

        $task->update($request->validated());

        return redirect()
            ->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensureTaskBelongsToProject($project, $task);

        $task->delete();

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', 'Task deleted successfully.');
    }

    private function ensureProjectOwner(Project $project): void
    {
        abort_unless($project->owner_id === Auth::id(), 403);
    }

    private function ensureTaskBelongsToProject(Project $project, Task $task): void
    {
        abort_unless($task->project_id === $project->id, 404);
    }
}
