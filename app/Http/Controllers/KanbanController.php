<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\KanbanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KanbanController extends Controller
{
    public function __construct(
        protected KanbanService $kanbanService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'project_id' => $request->query('project_id'),
            'team_id' => $request->query('team_id'),
            'assigned_to' => $request->query('assigned_to'),
            'priority' => $request->query('priority'),
            'overdue' => $request->boolean('overdue'),
        ];

        $boardData = $this->kanbanService->getBoardColumns(Auth::user(), $filters);

        return view('kanban.index', array_merge([
            'filters' => $filters,
        ], $boardData));
    }

    public function updateStatus(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => ['required', 'string', 'in:todo,in_progress,review,done,completed,in_review'],
        ]);

        $updatedTask = $this->kanbanService->updateTaskStatus(
            Auth::user(),
            $task,
            $request->input('status')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة المهمة بنجاح.',
                'task' => [
                    'id' => $updatedTask->id,
                    'status' => $updatedTask->status,
                ],
            ]);
        }

        return back()->with('success', 'تم نقل المهمة بنجاح.');
    }
}
