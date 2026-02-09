<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use App\Models\Project;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private ExportService $exportService)
    {
    }

    /**
     * Export messages as CSV
     */
    public function messagesCSV(Project $project)
    {
        $this->authorize('view', $project);

        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : null;
        $deviceId = request('device_id') ? (int)request('device_id') : null;

        $csv = $this->exportService->messagesAsCSV($project->id, $startDate, $endDate, $deviceId);

        return $this->downloadCSV(
            $csv,
            "messages_{$project->project_key}_" . now()->format('Y-m-d-His') . '.csv'
        );
    }

    /**
     * Export usage logs as CSV
     */
    public function usageCSV(Project $project)
    {
        $this->authorize('view', $project);

        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : null;

        $csv = $this->exportService->usageAsCSV($project->id, $startDate, $endDate);

        return $this->downloadCSV(
            $csv,
            "usage_{$project->project_key}_" . now()->format('Y-m-d-His') . '.csv'
        );
    }

    /**
     * Export analytics summary as CSV
     */
    public function analyticsSummaryCSV(Project $project)
    {
        $this->authorize('view', $project);

        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : null;

        $csv = $this->exportService->analyticsSummaryAsCSV($project->id, $startDate, $endDate);

        return $this->downloadCSV(
            $csv,
            "analytics_summary_{$project->project_key}_" . now()->format('Y-m-d-His') . '.csv'
        );
    }

    /**
     * Export device activity as CSV
     */
    public function deviceActivityCSV(Project $project)
    {
        $this->authorize('view', $project);

        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : null;

        $csv = $this->exportService->deviceActivityAsCSV($project->id, $startDate, $endDate);

        return $this->downloadCSV(
            $csv,
            "device_activity_{$project->project_key}_" . now()->format('Y-m-d-His') . '.csv'
        );
    }

    /**
     * Export hourly statistics as CSV
     */
    public function hourlyStatsCSV(Project $project)
    {
        $this->authorize('view', $project);

        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : null;

        $csv = $this->exportService->hourlyStatsAsCSV($project->id, $startDate, $endDate);

        return $this->downloadCSV(
            $csv,
            "hourly_stats_{$project->project_key}_" . now()->format('Y-m-d-His') . '.csv'
        );
    }

    /**
     * Helper to download CSV
     */
    private function downloadCSV(string $csv, string $filename): StreamedResponse
    {
        return response()->streamDownload(
            fn() => echo $csv,
            $filename,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
