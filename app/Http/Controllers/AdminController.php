<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin system overview dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_users'       => User::count(),
            'total_clients'     => User::where('account_type', 'client')->count(),
            'total_freelancers' => User::where('account_type', 'freelancer')->count(),
            'total_projects'    => Project::count(),
            'total_tasks'       => Task::count(),
            'total_contracts'   => Contract::count(),
            'total_admins'      => User::where('is_admin', true)->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();

        return view('admin.index', compact('stats', 'recentUsers'));
    }

    /**
     * Admin user management list.
     */
    public function users(Request $request): View
    {
        $query = User::query()
            ->with('freelancerProfile')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            if ($type === 'banned') {
                $query->where('is_banned', true);
            } else {
                $query->where('account_type', $type);
            }
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * Toggle admin status for a user (cannot demote self).
     */
    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك تغيير صلاحياتك الخاصة.');
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        $message = $user->is_admin
            ? "تم منح صلاحية المدير لـ {$user->name} بنجاح."
            : "تم سحب صلاحية المدير من {$user->name} بنجاح.";

        return back()->with('success', $message);
    }

    /**
     * Ban / unban a user account.
     */
    public function toggleBan(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك حظر حسابك الخاص.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'لا يمكنك حظر حساب مدير آخر للنظام.');
        }

        $user->update(['is_banned' => ! $user->is_banned]);

        $message = $user->is_banned
            ? "تم حظر حساب {$user->name} بنجاح."
            : "تم رفع الحظر عن {$user->name} بنجاح.";

        return back()->with('success', $message);
    }

    /**
     * Admin projects management list.
     */
    public function projects(Request $request): View
    {
        $query = Project::query()
            ->with('owner')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projects = $query->paginate(20)->withQueryString();

        return view('admin.projects', compact('projects'));
    }
}
