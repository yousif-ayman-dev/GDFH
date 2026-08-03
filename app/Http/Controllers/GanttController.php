<?php

namespace App\Http\Controllers;

use App\Services\GanttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GanttController extends Controller
{
    public function __construct(
        protected GanttService $ganttService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'zoom' => $request->query('zoom', 'month'),
            'project_id' => $request->query('project_id'),
            'search' => $request->query('search'),
        ];

        $ganttData = $this->ganttService->getGanttData(Auth::user(), $filters);

        return view('gantt.index', array_merge([
            'filters' => $filters,
        ], $ganttData));
    }
}
