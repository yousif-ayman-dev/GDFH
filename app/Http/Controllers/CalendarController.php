<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        $view = $request->query('view', 'month');
        if (! in_array($view, ['month', 'week', 'agenda'], true)) {
            $view = 'month';
        }

        $filters = [
            'type' => $request->query('type'),
            'assigned_to_me' => $request->boolean('assigned_to_me'),
            'overdue' => $request->boolean('overdue'),
            'status' => $request->query('status'),
            'project_id' => $request->query('project_id'),
        ];

        $todayEvents = $this->calendarService->getTodayEvents($user);
        $upcomingEvents = $this->calendarService->getUpcomingEvents($user, 7);

        $calendarData = [];

        if ($view === 'month') {
            $month = $request->query('month');
            $calendarData = $this->calendarService->getCalendarGrid($user, $month, $filters);
        } elseif ($view === 'week') {
            $weekStart = $request->query('week_start');
            $calendarData = $this->calendarService->getWeeklySchedule($user, $weekStart, $filters);
        } else {
            // Agenda View
            $filters['month'] = $request->query('month');
            $calendarData = [
                'agenda' => $this->calendarService->getAgendaView($user, $filters),
            ];
        }

        return view('calendar.index', array_merge([
            'currentView' => $view,
            'filters' => $filters,
            'todayEvents' => $todayEvents,
            'upcomingEvents' => $upcomingEvents,
            'user_projects' => $this->calendarService->getUserProjects($user),
        ], $calendarData));
    }

    public function storeEvent(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('create', Event::class);

        $validated = $this->validateEventRequest($request, $user);

        $event = Event::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
            'color' => $validated['color'] ?? 'copper',
            'location' => $validated['location'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحدث بنجاح.',
                'event' => $event,
            ]);
        }

        return back()->with('success', 'تم إضافة الحدث بنجاح إلى التقويم.');
    }

    public function updateEvent(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('update', $event);

        $validated = $this->validateEventRequest($request, $user, $event);

        $event->update([
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
            'color' => $validated['color'] ?? 'copper',
            'location' => $validated['location'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحدث بنجاح.',
                'event' => $event,
            ]);
        }

        return back()->with('success', 'تم تحديث الحدث بنجاح.');
    }

    public function destroyEvent(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الحدث بنجاح.',
            ]);
        }

        return back()->with('success', 'تم حذف الحدث من التقويم.');
    }

    protected function validateEventRequest(Request $request, $user, ?Event $event = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'color' => ['nullable', 'string', 'in:copper,blue,amber,purple,emerald,red'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'location' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'عنوان الحدث مطلوب.',
            'start_at.required' => 'تاريخ وتوقيت البداية مطلوب.',
            'end_at.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يطابق تاريخ البداية.',
            'project_id.exists' => 'المشروع المحدد غير موجود.',
        ]);

        if (! empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if ($project && ! $user->can('view', $project)) {
                abort(403, 'غير مصرح لك بربط الحدث بهذا المشروع.');
            }
        }

        return $validated;
    }
}
