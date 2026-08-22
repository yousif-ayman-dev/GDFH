<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $reportData = $this->reportService->generateReport(Auth::user(), $filters);

        return view('reports.index', array_merge([
            'filters' => $filters,
        ], $reportData));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request);

        $reportData = $this->reportService->generateReport(Auth::user(), $filters);

        $filename = 'tasker_report_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($reportData) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel Arabic compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Title & Metadata
            fputcsv($handle, ['تقرير تحليلات الإنتاجية — Tasker Enterprise']);
            fputcsv($handle, ['تاريخ التصدير', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['المستخدم', Auth::user()->name]);
            fputcsv($handle, []);

            // KPIs Summary Section
            fputcsv($handle, ['مؤشر الإنتاجية (%)', 'معدل الإنجاز (%)', 'متوسط زمن الإنجاز (أيام)', 'إجمالي المشاريع', 'إجمالي المهام', 'المهام المكتملة', 'ساعات العمل المسجلة']);
            $kpis = $reportData['kpis'];
            fputcsv($handle, [
                $kpis['productivity_score'],
                $kpis['completion_rate'],
                $kpis['avg_completion_days'],
                $kpis['total_projects'],
                $kpis['total_tasks'],
                $kpis['completed_tasks'],
                $kpis['total_tracked_hours'],
            ]);

            fputcsv($handle, []);

            // Projects Performance Section
            fputcsv($handle, ['--- ملخص أداء المشاريع ---']);
            fputcsv($handle, ['معرف المشروع', 'عنوان المشروع', 'الحالة', 'المالك', 'نسبة الإنجاز (%)']);
            foreach ($reportData['reports']['projects'] as $project) {
                fputcsv($handle, [
                    $project->id,
                    $project->title,
                    $project->status,
                    $project->owner?->name ?? 'غير محدد',
                    $project->progress(),
                ]);
            }

            fputcsv($handle, []);

            // User Leaderboard Section
            fputcsv($handle, ['--- إنتاجية أعضاء الفريق ---']);
            fputcsv($handle, ['اسم العضو', 'المهام المكتملة', 'إجمالي المهام', 'نسبة الإنجاز (%)']);
            foreach ($reportData['reports']['user_leaderboard'] as $item) {
                fputcsv($handle, [
                    $item['user']->name ?? 'مستخدم',
                    $item['completed_tasks'],
                    $item['total_tasks'],
                    $item['completion_rate'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $reportData = $this->reportService->generateReport(Auth::user(), $filters);

        return view('reports.pdf', array_merge([
            'filters' => $filters,
            'generated_at' => now(),
            'user' => Auth::user(),
        ], $reportData));
    }

    protected function validateFilters(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'project_id' => ['nullable', 'integer'],
            'team_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
