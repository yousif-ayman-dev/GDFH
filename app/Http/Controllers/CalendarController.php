<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
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
        ], $calendarData));
    }
}
