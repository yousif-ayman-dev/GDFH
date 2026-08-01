<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Services\TeamInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamInvitationController extends Controller
{
    public function __construct(
        protected TeamInvitationService $invitationService
    ) {}

    /**
     * Store a new team invitation.
     */
    public function store(
        StoreTeamInvitationRequest $request,
        Team $team
    ): RedirectResponse {
        $this->authorize('create', [TeamInvitation::class, $team]);

        $validated = $request->validated();
        $invitee = User::findOrFail($validated['invitee_id']);

        $this->invitationService->sendInvitation(
            $team,
            Auth::user(),
            $invitee,
            $validated
        );

        return back()->with('success', 'تم إرسال الدعوة بنجاح.');
    }

    /**
     * Accept a team invitation.
     */
    public function accept(
        Request $request,
        TeamInvitation $invitation
    ): RedirectResponse {
        $this->authorize('accept', $invitation);

        $this->invitationService->acceptInvitation($invitation);

        return back()->with('success', 'تم قبول الدعوة والانضمام إلى الفريق.');
    }

    /**
     * Reject a team invitation.
     */
    public function reject(
        Request $request,
        TeamInvitation $invitation
    ): RedirectResponse {
        $this->authorize('reject', $invitation);

        $this->invitationService->rejectInvitation($invitation);

        return back()->with('success', 'تم رفض الدعوة.');
    }

    /**
     * Cancel a team invitation.
     */
    public function cancel(
        Request $request,
        TeamInvitation $invitation
    ): RedirectResponse {
        $this->authorize('cancel', $invitation);

        $this->invitationService->cancelInvitation($invitation);

        return back()->with('success', 'تم إلغاء الدعوة.');
    }
}
