<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TeamProjectController extends Controller
{
    public function attach(Team $team, Project $project): RedirectResponse
    {
        $this->ensureTeamOwner($team);

        $alreadyAttached = $project->teams()->where('teams.id', $team->id)->exists();

        if ($alreadyAttached) {
            return redirect()
                ->route('teams.show', $team)
                ->withErrors([
                    'project_id' => 'This project is already linked to the team.',
                ])
                ->withInput();
        }

        $project->teams()->attach($team->id, [
            'is_primary' => false,
            'joined_at' => now(),
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Project linked to team successfully.');
    }

    public function detach(Team $team, Project $project): RedirectResponse
    {
        $this->ensureTeamOwner($team);

        $project->teams()->detach($team->id);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Project unlinked from team successfully.');
    }

    private function ensureTeamOwner(Team $team): void
    {
        abort_unless($team->owner_id === Auth::id(), 403);
    }
}
