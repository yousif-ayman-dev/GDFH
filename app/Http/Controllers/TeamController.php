<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
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
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(6)),
        ]);

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
        ]);

        return view('teams.show', compact('team'));
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

        if (
            isset($data['name']) &&
            $data['name'] !== $team->name
        ) {
            $data['slug'] =
                Str::slug($data['name']) . '-' . Str::lower(Str::random(6));
        }

        $team->update($data);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->ensureOwner($team);

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
}
