<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        protected \App\Services\ActivityService $activityService
    ) {}

    public function index(): View
    {
        $teams = Team::query()
            ->where('owner_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('teams.index', compact('teams'));
    }

    public function create(): View
    {
        return view('teams.create');
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $team = Team::create([
            ...$data,
            'owner_id' => Auth::id(),
            'logo_path' => $this->storeLogo($request->file('logo')),
        ]);

        $this->activityService->logTeamCreated(Auth::user(), $team);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team created successfully.');
    }

    public function show(Team $team): View
    {
        $this->ensureOwner($team);

        $team->load([
            'owner',
            'memberships.user',
            'projects.owner',
            'tasks.project',
            'tasks.assignee',
            'invitations.invitee',
            'invitations.inviter',
        ]);

        $availableProjects = Project::query()
            ->where('owner_id', Auth::id())
            ->whereNotIn('id', $team->projects->pluck('id'))
            ->get();

        return view('teams.show', compact('team', 'availableProjects'));
    }

    public function edit(Team $team): View
    {
        $this->ensureOwner($team);

        return view('teams.edit', compact('team'));
    }

    public function update(
        UpdateTeamRequest $request,
        Team $team
    ): RedirectResponse {
        $this->ensureOwner($team);

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $this->deleteLogo($team->logo_path);
            $data['logo_path'] = $this->storeLogo($request->file('logo'));
        }

        $team->update($data);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->ensureOwner($team);

        $this->deleteLogo($team->logo_path);
        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    private function ensureOwner(Team $team): void
    {
        abort_unless(
            $team->owner_id === Auth::id(),
            403
        );
    }

    private function storeLogo(?UploadedFile $logo): ?string
    {
        if ($logo === null) {
            return null;
        }

        return $logo->store('teams', 'public');
    }

    private function deleteLogo(?string $logoPath): void
    {
        if ($logoPath === null || $logoPath === '') {
            return;
        }

        Storage::disk('public')->delete($logoPath);
    }
}
