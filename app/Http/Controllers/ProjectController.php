<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->where('owner_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $project = Project::create([
            ...$data,
            'owner_id' => Auth::id(),
            'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(6)),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $this->ensureOwner($project);

        $project->load([
            'owner',
            'memberRecords.user',
        ]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $this->ensureOwner($project);

        return view('projects.edit', compact('project'));
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): RedirectResponse {
        $this->ensureOwner($project);

        $data = $request->validated();

        if (
            isset($data['title']) &&
            $data['title'] !== $project->title
        ) {
            $data['slug'] =
                Str::slug($data['title']) . '-' . Str::lower(Str::random(6));
        }

        $project->update($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->ensureOwner($project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    private function ensureOwner(Project $project): void
    {
        abort_unless(
            $project->owner_id === Auth::id(),
            403
        );
    }
}
