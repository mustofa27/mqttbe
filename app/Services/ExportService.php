<?php

namespace App\Services;

use App\Models\Message;
use App\Models\UsageLog;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExportService
{
    /**
     * Export messages to CSV
     */
    public function messagesAsCSV(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?int $deviceId = null): string
    {
        $messages = $this->getFilteredMessages($projectId, $startDate, $endDate, $deviceId);

        $csv = "ID,Project ID,Device ID,Topic,Payload,QoS,Retained,MQTT Topic,Created At,Expires At\n";

        foreach ($messages as $message) {
            $csv .= implode(',', [
                $message->id,
                $message->project_id,
                $message->device_id,
                $message->topic_id,
                '"' . str_replace('"', '""', $message->payload) . '"',
                $message->qos,
                $message->retained ? 'Yes' : 'No',
                $message->mqtt_topic,
                $message->created_at,
                $message->expires_at,
            ]) . "\n";
        }

        return $csv;
    }

    /**
     * Export usage logs to CSV
     */
    public function usageAsCSV(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $logs = $this->getFilteredUsageLogs($projectId, $startDate, $endDate);

        $csv = "ID,Project ID,User ID,Period Type,Period Start,Period End,Message Count\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->project_id,
                $log->user_id,
                $log->period_type,
                $log->period_start,
                $log->period_end,
                $log->message_count,
            ]) . "\n";
        }

        return $csv;
    }

    /**
     * Export analytics summary to CSV
     */
    public function analyticsSummaryAsCSV(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $start = $startDate ?? Carbon::now()->subDays(30);
        $end = $endDate ?? Carbon::now();

        $messages = Message::where('project_id', $projectId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $devices = $messages->groupBy('device_id')
            ->map(function ($msgs) {
                return $msgs->count();
            });

        $topics = $messages->groupBy('topic_id')
            ->map(function ($msgs) {
                return $msgs->count();
            });

        $csv = "Analytics Summary Report\n";
        $csv .= "Project ID,{$projectId}\n";
        $csv .= "Period Start,{$start->format('Y-m-d H:i:s')}\n";
        $csv .= "Period End,{$end->format('Y-m-d H:i:s')}\n";
        $csv .= "Total Messages,{$messages->count()}\n";
        $csv .= "Unique Devices,{$devices->count()}\n";
        $csv .= "Unique Topics,{$topics->count()}\n";
        $csv .= "Avg QoS," . number_format($messages->avg('qos'), 2) . "\n";
        $csv .= "\n";

        $csv .= "Messages by Device\n";
        $csv .= "Device ID,Message Count\n";
        foreach ($devices as $deviceId => $count) {
            $csv .= "{$deviceId},{$count}\n";
        }

        $csv .= "\n";
        $csv .= "Messages by Topic\n";
        $csv .= "Topic ID,Message Count\n";
        foreach ($topics as $topicId => $count) {
            $csv .= "{$topicId},{$count}\n";
        }

        return $csv;
    }

    /**
     * Export hourly stats to CSV
     */
    public function hourlyStatsAsCSV(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $start = $startDate ?? Carbon::now()->subDays(7);
        $end = $endDate ?? Carbon::now();

        $logs = UsageLog::where('project_id', $projectId)
            ->where('period_type', 'hourly')
            ->whereBetween('period_start', [$start, $end])
            ->orderBy('period_start')
            ->get();

        $csv = "Hour,Message Count,Date\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->period_start->format('Y-m-d H:i:s'),
                $log->message_count,
                $log->period_start->format('Y-m-d'),
            ]) . "\n";
        }

        return $csv;
    }

    /**
     * Get filtered messages for export/filtering
     */
    public function getFilteredMessages(
        int $projectId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $deviceId = null,
        ?int $topicId = null,
        ?int $qos = null
    ): Collection {
        $query = Message::where('project_id', $projectId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        if ($topicId) {
            $query->where('topic_id', $topicId);
        }

        if ($qos !== null) {
            $query->where('qos', $qos);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get filtered usage logs
     */
    public function getFilteredUsageLogs(
        int $projectId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        string $periodType = 'daily'
    ): Collection {
        $query = UsageLog::where('project_id', $projectId)
            ->where('period_type', $periodType);

        if ($startDate) {
            $query->where('period_start', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('period_end', '<=', $endDate);
        }

        return $query->orderBy('period_start', 'desc')->get();
    }

    /**
     * Get summary statistics for a project
     */
    public function getProjectSummary(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $start = $startDate ?? Carbon::now()->subDays(30);
        $end = $endDate ?? Carbon::now();

        $messages = Message::where('project_id', $projectId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $devices = $messages->groupBy('device_id')->count();
        $topics = $messages->groupBy('topic_id')->count();
        $qos0 = $messages->where('qos', 0)->count();
        $qos1 = $messages->where('qos', 1)->count();
        $qos2 = $messages->where('qos', 2)->count();
        $retained = $messages->where('retained', true)->count();

        return [
            'total_messages' => $messages->count(),
            'unique_devices' => $devices,
            'unique_topics' => $topics,
            'avg_payload_size' => $messages->map(fn($m) => strlen($m->payload))->avg(),
            'qos_distribution' => [
                'qos_0' => $qos0,
                'qos_1' => $qos1,
                'qos_2' => $qos2,
            ],
            'retained_messages' => $retained,
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    /**
     * Get device activity for export
     */
    public function getDeviceActivityReport(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $start = $startDate ?? Carbon::now()->subDays(30);
        $end = $endDate ?? Carbon::now();

        $messages = Message::where('project_id', $projectId)
            ->whereBetween('created_at', [$start, $end])
            ->with('device')
            ->get();

        $deviceReport = [];

        foreach ($messages->groupBy('device_id') as $deviceId => $deviceMessages) {
            $device = $deviceMessages->first()?->device;
            $deviceReport[] = [
                'device_id' => $device?->device_id ?? 'Unknown',
                'device_name' => $device?->device_id ?? 'Unknown',
                'message_count' => $deviceMessages->count(),
                'first_message' => $deviceMessages->min('created_at'),
                'last_message' => $deviceMessages->max('created_at'),
                'avg_qos' => $deviceMessages->avg('qos'),
                'qos_0_count' => $deviceMessages->where('qos', 0)->count(),
                'qos_1_count' => $deviceMessages->where('qos', 1)->count(),
                'qos_2_count' => $deviceMessages->where('qos', 2)->count(),
            ];
        }

        usort($deviceReport, fn($a, $b) => $b['message_count'] <=> $a['message_count']);

        return $deviceReport;
    }

    /**
     * Device activity as CSV
     */
    public function deviceActivityAsCSV(int $projectId, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $report = $this->getDeviceActivityReport($projectId, $startDate, $endDate);

        $csv = "Device ID,Device Name,Message Count,First Message,Last Message,Avg QoS,QoS 0,QoS 1,QoS 2\n";

        foreach ($report as $device) {
            $csv .= implode(',', [
                $device['device_id'],
                '"' . str_replace('"', '""', $device['device_name']) . '"',
                $device['message_count'],
                $device['first_message']?->format('Y-m-d H:i:s') ?? 'N/A',
                $device['last_message']?->format('Y-m-d H:i:s') ?? 'N/A',
                number_format($device['avg_qos'], 2),
                $device['qos_0_count'],
                $device['qos_1_count'],
                $device['qos_2_count'],
            ]) . "\n";
        }

        return $csv;
    }
}
