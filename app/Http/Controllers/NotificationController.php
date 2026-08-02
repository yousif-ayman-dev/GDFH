<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(): View
    {
        $notifications = Auth::user()
            ->appNotifications()
            ->with('sender')
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(AppNotification $notification): RedirectResponse
    {
        $this->authorize('update', $notification);

        $this->notificationService->markAsRead($notification);

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back()->with('success', 'تم تحديد الإشعار كمقروء.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return back()->with('success', "تم تحديد {$count} إشعار كمقروء.");
    }

    public function destroy(AppNotification $notification): RedirectResponse
    {
        $this->authorize('delete', $notification);

        $this->notificationService->delete($notification);

        return back()->with('success', 'تم حذف الإشعار بنجاح.');
    }
}
