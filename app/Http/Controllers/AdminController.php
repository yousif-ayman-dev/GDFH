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
        $totalProjects = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalContracts = Contract::count();
        $completedContracts = Contract::where('status', 'completed')->count();

        $totalProjectValue = (float) (Project::whereNotNull('budget')->sum('budget') ?: Project::sum('budget_min'));
        $totalContractValue = (float) Contract::sum('amount');
        $releasedFunds = (float) Contract::where('status', 'completed')->sum('amount');
        $escrowHeld = (float) Contract::where('status', 'active')->sum('amount');
        $refundedFunds = (float) Contract::where('status', 'cancelled')->sum('amount');

        $commissionRate = (float) config('monetization.commission_rate', env('PLATFORM_COMMISSION_RATE', 0.10));
        $platformCommissionTotal = (float) round($releasedFunds * $commissionRate, 2);

        $stats = [
            'total_users'             => User::count(),
            'total_clients'           => User::where('account_type', 'client')->count(),
            'total_freelancers'       => User::where('account_type', 'freelancer')->count(),
            'verified_users'          => User::where('is_verified', true)->count(),
            'unverified_users'        => User::where(fn ($q) => $q->whereNull('is_verified')->orWhere('is_verified', false))->count(),
            'active_users'            => User::where('is_banned', false)->whereNotNull('onboarded_at')->count(),
            'total_projects'          => $totalProjects,
            'active_projects'         => Project::whereIn('status', ['open', 'in_progress', 'review'])->count(),
            'completed_projects'      => $completedProjects,
            'draft_projects'          => Project::where('status', 'draft')->count(),
            'completion_rate'         => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0,
            'total_tasks'             => Task::count(),
            'completed_tasks'         => Task::whereIn('status', ['completed', 'done'])->count(),
            'total_contracts'         => $totalContracts,
            'active_contracts'        => Contract::where('status', 'active')->count(),
            'completed_contracts'     => $completedContracts,
            'total_admins'            => User::where('is_admin', true)->count(),
            // Financial KPIs
            'total_project_value'     => $totalProjectValue,
            'total_contract_value'    => $totalContractValue,
            'escrow_held'             => $escrowHeld,
            'released_funds'          => $releasedFunds,
            'refunded_funds'          => $refundedFunds,
            'commission_rate'         => $commissionRate,
            'platform_commission'     => $platformCommissionTotal,
            'avg_project_value'       => $totalProjects > 0 ? round($totalProjectValue / $totalProjects, 2) : 0,
            'user_growth_30_days'     => User::where('created_at', '>=', now()->subDays(30))->count(),
            'project_growth_30_days'  => Project::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentProjects = Project::with('owner')->latest()->limit(5)->get();

        $topCategories = Project::query()
            ->select('category', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Monthly registration trends over last 6 months
        $monthlyRegistrations = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        });

        // Monthly project creation trends over last 6 months
        $monthlyProjects = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $count = Project::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        });

        return view('admin.index', compact(
            'stats',
            'recentUsers',
            'recentProjects',
            'topCategories',
            'monthlyRegistrations',
            'monthlyProjects'
        ));
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

        // Send notification to user on role promotion/demotion
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            if ($user->is_admin) {
                $notificationService->sendNotification(
                    $user,
                    'تمت ترقية حسابك إلى مدير منصة 👑',
                    'تهانينا! قام المدير (' . $request->user()->name . ') بمنح حسابك صلاحيات الإدارة الشاملة على منصة Tasker.',
                    route('admin.dashboard')
                );
            } else {
                $notificationService->sendNotification(
                    $user,
                    'تحديث صلاحيات الحساب ℹ️',
                    'تم تعديل صلاحيات حسابك على منصة Tasker بواسطة الإدارة.',
                    route('dashboard')
                );
            }
        } catch (\Throwable $e) {}

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
