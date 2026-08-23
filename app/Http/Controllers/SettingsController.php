<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the system settings hub page.
     */
    public function index(Request $request): View
    {
        return view('settings.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'preferences' => ['nullable', 'array'],
            'preferences.email' => ['nullable', 'boolean'],
            'preferences.in_app' => ['nullable', 'boolean'],
            'preferences.task_assigned' => ['nullable', 'boolean'],
            'preferences.team_invite' => ['nullable', 'boolean'],
        ]);

        $preferences = [
            'email' => $request->boolean('preferences.email'),
            'in_app' => $request->boolean('preferences.in_app'),
            'task_assigned' => $request->boolean('preferences.task_assigned'),
            'team_invite' => $request->boolean('preferences.team_invite'),
        ];

        $user->notification_preferences = $preferences;
        $user->save();

        return back()->with('status', 'notifications-updated');
    }
}
