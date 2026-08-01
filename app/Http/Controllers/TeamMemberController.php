<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\TeamMemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamMemberController extends Controller
{
    public function __construct(
        protected TeamMemberService $memberService
    ) {}

    /**
     * Store a newly created team member.
     */
    public function store(
        StoreTeamMemberRequest $request,
        Team $team
    ): RedirectResponse {
        $this->authorize('addMember', $team);

        $data = $request->validated();
        $user = User::findOrFail($data['user_id']);

        $this->memberService->addMember(
            $team,
            $user,
            $data['role'] ?? 'member',
            $data['status'] ?? 'active',
            Auth::id()
        );

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'تم إضافة العضو بنجاح.');
    }

    /**
     * Update team member settings (role/status).
     */
    public function update(
        UpdateTeamMemberRequest $request,
        Team $team,
        TeamMember $member
    ): RedirectResponse {
        $this->ensureMemberBelongsToTeam($team, $member);

        $data = $request->validated();

        if (isset($data['role'])) {
            $this->authorize('updateMemberRole', [$team, $member, $data['role']]);
            $this->memberService->updateRole($team, $member, $data['role']);
        }

        if (isset($data['status'])) {
            $this->authorize('update', $team);
            $member->update(['status' => $data['status']]);
        }

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'تم تحديث بيانات العضو بنجاح.');
    }

    /**
     * Explicit route to update a member's role.
     */
    public function updateRole(
        Request $request,
        Team $team,
        TeamMember $member
    ): RedirectResponse {
        $this->ensureMemberBelongsToTeam($team, $member);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,manager,member,viewer'],
        ], [
            'role.required' => 'يرجى اختيار الدور.',
            'role.in' => 'الدور المحدد غير صالح.',
        ]);

        $this->authorize('updateMemberRole', [$team, $member, $validated['role']]);

        $this->memberService->updateRole($team, $member, $validated['role']);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'تم تغيير دور العضو بنجاح.');
    }

    /**
     * Remove a member from the team.
     */
    public function destroy(Team $team, TeamMember $member): RedirectResponse
    {
        $this->ensureMemberBelongsToTeam($team, $member);
        $this->authorize('removeMember', [$team, $member]);

        $this->memberService->removeMember($team, $member);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'تم إزالة العضو من الفريق بنجاح.');
    }

    /**
     * Transfer team ownership to another member.
     */
    public function transferOwnership(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('transferOwnership', $team);

        $validated = $request->validate([
            'new_owner_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'new_owner_id.required' => 'يرجى اختيار المالك الجديد.',
            'new_owner_id.exists' => 'المستخدم المحدد غير موجود.',
        ]);

        $newOwner = User::findOrFail($validated['new_owner_id']);

        $this->memberService->transferOwnership($team, $newOwner);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'تم نقل ملكية الفريق بنجاح.');
    }

    private function ensureMemberBelongsToTeam(Team $team, TeamMember $member): void
    {
        abort_unless(
            $member->team_id === $team->id,
            404
        );
    }
}
