<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectTeamController extends Controller
{
    public function store(Project $project): RedirectResponse
    {
        $this->ensureOwner($project);

        $data = request()->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $team = Team::query()->findOrFail($data['team_id']);

        if ($team->owner_id !== Auth::id()) {
            return redirect()
                ->route('projects.show', $project)
                ->withErrors([
                    'team_id' => 'The selected team is not owned by you.',
                ])
                ->withInput();
        }

        $alreadyAttached = $project->teams()->where('teams.id', $team->id)->exists();

        if ($alreadyAttached) {
            return redirect()
                ->route('projects.show', $project)
                ->withErrors([
                    'team_id' => 'This team is already attached to the project.',
                ])
                ->withInput();
        }

        $project->teams()->attach($team->id, [
            'is_primary' => false,
            'joined_at' => now(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Team attached successfully.');
    }

    public function destroy(Project $project, Team $team): RedirectResponse
    {
        $this->ensureOwner($project);

        $attached = $project->teams()->where('teams.id', $team->id)->exists();

        abort_unless($attached, 404);

        $project->teams()->detach($team->id);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Team detached successfully.');
    }

    private function ensureOwner(Project $project): void
    {
        abort_unless($project->owner_id === Auth::id(), 403);
    }
}
