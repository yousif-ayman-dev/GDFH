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

    public function poll(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $this->notificationService->unreadCount($user);

        $notifications = $user->appNotifications()
            ->with('sender')
            ->unread()
            ->latest()
            ->take(5)
            ->get()
            ->map(function (AppNotification $n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'description' => $n->description,
                    'type' => $n->type,
                    'priority' => $n->priority,
                    'action_url' => $n->action_url,
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'sender_name' => $n->sender?->name ?? 'نظام Tasker',
                    'sender_avatar' => $n->sender?->avatar_url,
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsReadJson(AppNotification $notification): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $notification);

        $this->notificationService->markAsRead($notification);
        $unreadCount = $this->notificationService->unreadCount(Auth::user());

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllAsReadJson(): \Illuminate\Http\JsonResponse
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
