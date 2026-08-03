<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Worklog;
use App\Services\TimeTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TimeTrackingController extends Controller
{
    public function __construct(
        protected TimeTrackingService $timeTrackingService
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        $activeTimer = $this->timeTrackingService->getActiveTimer($user);

        // Fetch accessible projects for user
        $projects = Project::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('memberRecords', function ($mq) use ($user) {
                      $mq->where('user_id', $user->id)->where('status', 'active');
                  });
            })
            ->latest()
            ->get(['id', 'title']);

        $tasks = Task::query()
            ->whereIn('project_id', $projects->pluck('id'))
            ->whereNotIn('status', ['completed', 'done', 'cancelled'])
            ->get(['id', 'title', 'project_id']);

        $worklogs = Worklog::query()
            ->where('user_id', $user->id)
            ->with(['project', 'task'])
            ->latest()
            ->paginate(15);

        $weeklySummary = $this->timeTrackingService->weeklySummary($user);
        $monthlySummary = $this->timeTrackingService->monthlySummary($user);
        $analytics = $this->timeTrackingService->getAnalytics($user);

        return view('time-tracking.index', compact(
            'activeTimer',
            'projects',
            'tasks',
            'worklogs',
            'weeklySummary',
            'monthlySummary',
            'analytics'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $project = Project::findOrFail($request->input('project_id'));
        $task = $request->input('task_id') ? Task::find($request->input('task_id')) : null;

        $this->timeTrackingService->startTimer(
            Auth::user(),
            $project,
            $task,
            $request->input('notes'),
            $request->boolean('is_billable', true)
        );

        return back()->with('success', 'تم تشغيل المؤقت المباشر بنجاح.');
    }

    public function pause(Worklog $worklog): RedirectResponse
    {
        $this->authorize('update', $worklog);

        $this->timeTrackingService->pauseTimer($worklog);

        return back()->with('success', 'تم إيقاف المؤقت مؤقتاً.');
    }

    public function resume(Worklog $worklog): RedirectResponse
    {
        $this->authorize('update', $worklog);

        $this->timeTrackingService->resumeTimer($worklog);

        return back()->with('success', 'تم استئناف المؤقت بنجاح.');
    }

    public function stop(Request $request, Worklog $worklog): RedirectResponse
    {
        $this->authorize('update', $worklog);

        $this->timeTrackingService->stopTimer($worklog, $request->input('notes'));

        return back()->with('success', 'تم إيقاف وحفظ سجّل العمل بنجاح.');
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['nullable', 'boolean'],
            'date' => ['nullable', 'date'],
        ]);

        $project = Project::findOrFail($request->input('project_id'));
        $task = $request->input('task_id') ? Task::find($request->input('task_id')) : null;

        $this->timeTrackingService->createManualWorklog(
            Auth::user(),
            $project,
            $task,
            (int) $request->input('duration_minutes'),
            $request->input('notes'),
            $request->boolean('is_billable', true),
            $request->input('date')
        );

        return back()->with('success', 'تم إدخال سجّل العمل اليدوي بنجاح.');
    }

    public function destroy(Worklog $worklog): RedirectResponse
    {
        $this->authorize('delete', $worklog);

        $this->timeTrackingService->deleteWorklog($worklog);

        return back()->with('success', 'تم حذف سجّل العمل بنجاح.');
    }
}
