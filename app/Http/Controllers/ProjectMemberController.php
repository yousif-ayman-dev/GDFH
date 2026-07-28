<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectMemberController extends Controller
{
    public function store(
        StoreProjectMemberRequest $request,
        Project $project
    ): RedirectResponse {
        $this->ensureOwner($project);

        $data = $request->validated();

        if ($project->owner_id === (int) $data['user_id']) {
            throw ValidationException::withMessages([
                'user_id' => 'The project owner cannot be added as a member.',
            ]);
        }

        $alreadyMember = ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'user_id' => 'This user is already a member of the project.',
            ]);
        }

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'status' => 'active',
            'invited_by' => Auth::id(),
            'joined_at' => now(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project member added successfully.');
    }

    public function update(
        UpdateProjectMemberRequest $request,
        Project $project,
        ProjectMember $member
    ): RedirectResponse {
        $this->ensureOwner($project);
        $this->ensureMemberBelongsToProject($project, $member);

        $data = $request->validated();

        if (
            isset($data['status']) &&
            $data['status'] === 'left' &&
            $member->status !== 'left'
        ) {
            $data['left_at'] = now();
        }

        if (
            isset($data['status']) &&
            $data['status'] !== 'left'
        ) {
            $data['left_at'] = null;
        }

        $member->update($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project member updated successfully.');
    }

    public function destroy(
        Project $project,
        ProjectMember $member
    ): RedirectResponse {
        $this->ensureOwner($project);
        $this->ensureMemberBelongsToProject($project, $member);

        $member->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project member removed successfully.');
    }

    private function ensureOwner(Project $project): void
    {
        abort_unless(
            $project->owner_id === Auth::id(),
            403
        );
    }

    private function ensureMemberBelongsToProject(
        Project $project,
        ProjectMember $member
    ): void {
        abort_unless(
            $member->project_id === $project->id,
            404
        );
    }
}
