<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService,
        protected ProjectWorkflowService $workflowService
    ) {}

    public function index(): View
    {
        $projects = Project::query()
            ->active()
            ->where('owner_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $data = $request->validated();
        $project = $this->projectService->createProject(Auth::user(), $data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'تم إنشاء المشروع بنجاح.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'owner',
            'team.memberships.user',
            'memberRecords.user',
            'tasks.assignee',
            'tasks.creator',
            'comments.user',
            'comments.replies.user',
        ]);

        $taskIds = $project->tasks->pluck('id');

        $activities = \App\Models\Activity::query()
            ->where(function ($q) use ($project) {
                $q->where('subject_type', Project::class)
                  ->where('subject_id', $project->id);
            })
            ->orWhere(function ($q) use ($taskIds) {
                $q->where('subject_type', \App\Models\Task::class)
                  ->whereIn('subject_id', $taskIds);
            })
            ->with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('projects.show', compact('project', 'activities'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): RedirectResponse {
        $this->authorize('update', $project);

        $data = $request->validated();
        $updatedProject = $this->projectService->updateProject($project, $data);

        return redirect()
            ->route('projects.show', $updatedProject)
            ->with('success', 'تم تحديث المشروع بنجاح.');
    }

    public function archive(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);

        $this->workflowService->archive($project);

        return back()->with('success', 'تم أرشفة المشروع بنجاح.');
    }

    public function restore(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('restore', $project);

        $this->workflowService->restore($project);

        return back()->with('success', 'تم إلغاء أرشفة المشروع واستعادته بنجاح.');
    }

    public function changeStatus(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,open,in_progress,on_hold,review,completed,cancelled,archived'],
        ], [
            'status.required' => 'يرجى اختيار حالة المشروع.',
            'status.in' => 'حالة المشروع غير صالحة.',
        ]);

        $this->workflowService->changeStatus($project, $validated['status']);

        return back()->with('success', 'تم تغيير حالة المشروع بنجاح.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'تم حذف المشروع بنجاح.');
    }
}
