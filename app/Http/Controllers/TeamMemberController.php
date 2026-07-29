<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TeamMemberController extends Controller
{
    public function store(
        StoreTeamMemberRequest $request,
        Team $team
    ): RedirectResponse {
        $this->ensureOwner($team);

        $data = $request->validated();

        if ($team->owner_id === (int) $data['user_id']) {
            throw ValidationException::withMessages([
                'user_id' => 'The team owner cannot be added as a member.',
            ]);
        }

        $alreadyMember = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'user_id' => 'This user is already a member of the team.',
            ]);
        }

        $status = $data['status'] ?? 'active';
        $joinedAt = null;

        if ($status === 'active') {
            $joinedAt = now();
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'status' => $status,
            'invited_by' => Auth::id(),
            'joined_at' => $joinedAt,
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team member added successfully.');
    }

    public function update(
        UpdateTeamMemberRequest $request,
        Team $team,
        TeamMember $member
    ): RedirectResponse {
        $this->ensureOwner($team);
        $this->ensureMemberBelongsToTeam($team, $member);

        $data = $request->validated();

        if (
            isset($data['status']) &&
            $data['status'] === 'active' &&
            $member->status !== 'active'
        ) {
            $data['joined_at'] = now();
        }

        if (
            isset($data['status']) &&
            $data['status'] !== 'active'
        ) {
            $data['joined_at'] = $member->joined_at;
        }

        $member->update($data);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(Team $team, TeamMember $member): RedirectResponse
    {
        $this->ensureOwner($team);
        $this->ensureMemberBelongsToTeam($team, $member);

        $member->delete();

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team member removed successfully.');
    }

    private function ensureOwner(Team $team): void
    {
        abort_unless(
            $team->owner_id === Auth::id(),
            403
        );
    }

    private function ensureMemberBelongsToTeam(Team $team, TeamMember $member): void
    {
        abort_unless(
            $member->team_id === $team->id,
            404
        );
    }
}
