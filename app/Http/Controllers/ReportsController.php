<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'project_id' => $request->query('project_id'),
            'team_id' => $request->query('team_id'),
            'user_id' => $request->query('user_id'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
        ];

        $reportData = $this->reportService->generateReport(Auth::user(), $filters);

        return view('reports.index', array_merge([
            'filters' => $filters,
        ], $reportData));
    }
}
