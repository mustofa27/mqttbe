<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Message;
use App\Models\Device;
use App\Models\Topic;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function __construct(private ExportService $exportService)
    {
    }

    /**
     * Get filtered messages with advanced filtering
     */
    public function messages(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'device_id' => 'nullable|integer',
            'topic_id' => 'nullable|integer',
            'qos' => 'nullable|integer|in:0,1,2',
            'retained' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,qos,payload_size',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;
        $perPage = $validated['per_page'] ?? 50;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        $messages = $this->exportService->getFilteredMessages(
            $project->id,
            $startDate,
            $endDate,
            $validated['device_id'] ?? null,
            $validated['topic_id'] ?? null,
            $validated['qos'] ?? null
        );

        if ($validated['retained'] ?? null) {
            $messages = $messages->filter(fn($m) => $m->retained === (bool)$validated['retained']);
        }

        // Sort
        $messages = $messages->sortBy(function ($message) use ($sortBy) {
            return match ($sortBy) {
                'qos' => $message->qos,
                'payload_size' => strlen($message->payload),
                default => $message->created_at,
            };
        });

        if ($sortOrder === 'asc') {
            $messages = $messages->reverse();
        }

        $total = $messages->count();
        $messages = $messages->slice(
            ($request->get('page', 1) - 1) * $perPage,
            $perPage
        )->values();

        return response()->json([
            'data' => $messages->map(fn($m) => [
                'id' => $m->id,
                'device_id' => $m->device_id,
                'topic_id' => $m->topic_id,
                'payload' => $m->payload,
                'qos' => $m->qos,
                'retained' => $m->retained,
                'mqtt_topic' => $m->mqtt_topic,
                'payload_size' => strlen($m->payload),
                'created_at' => $m->created_at,
            ]),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'page' => $request->get('page', 1),
                'last_page' => ceil($total / $perPage),
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'device_id' => $validated['device_id'] ?? null,
                'topic_id' => $validated['topic_id'] ?? null,
                'qos' => $validated['qos'] ?? null,
                'retained' => $validated['retained'] ?? null,
            ],
        ]);
    }

    /**
     * Get filtering options/available values
     */
    public function options(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $devices = Device::where('project_id', $project->id)
            ->select('id', 'device_id')
            ->orderBy('device_id')
            ->get();

        $topics = Topic::where('project_id', $project->id)
            ->select('id', 'code', 'template')
            ->orderBy('code')
            ->get();

        $qosOptions = [
            ['value' => 0, 'label' => 'QoS 0 (At Most Once)'],
            ['value' => 1, 'label' => 'QoS 1 (At Least Once)'],
            ['value' => 2, 'label' => 'QoS 2 (Exactly Once)'],
        ];

        return response()->json([
            'devices' => $devices,
            'topics' => $topics,
            'qos_options' => $qosOptions,
            'sort_options' => [
                ['value' => 'created_at', 'label' => 'Date Created'],
                ['value' => 'qos', 'label' => 'QoS Level'],
                ['value' => 'payload_size', 'label' => 'Payload Size'],
            ],
        ]);
    }

    /**
     * Get summary statistics based on filters
     */
    public function summary(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'device_id' => 'nullable|integer',
            'topic_id' => 'nullable|integer',
        ]);

        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        $summary = $this->exportService->getProjectSummary(
            $project->id,
            $startDate,
            $endDate
        );

        // Apply additional filters if needed
        if ($validated['device_id'] ?? null) {
            $filtered = Message::where('project_id', $project->id)
                ->where('device_id', $validated['device_id'])
                ->whereBetween('created_at', [$startDate ?? $summary['period_start'], $endDate ?? $summary['period_end']])
                ->get();

            $summary['total_messages'] = $filtered->count();
            $summary['unique_topics'] = $filtered->pluck('topic_id')->unique()->count();
            $summary['avg_payload_size'] = $filtered->map(fn($m) => strlen($m->payload))->avg();
        }

        return response()->json($summary);
    }

    /**
     * Device activity report with filters
     */
    public function deviceActivity(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_by' => 'nullable|string|in:message_count,last_activity,avg_qos',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        $report = $this->exportService->getDeviceActivityReport($project->id, $startDate, $endDate);

        // Apply sorting
        $sortBy = $validated['sort_by'] ?? 'message_count';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        usort($report, function ($a, $b) use ($sortBy, $sortOrder) {
            $aVal = $a[$sortBy] ?? 0;
            $bVal = $b[$sortBy] ?? 0;

            if (is_string($aVal)) {
                $comparison = strcmp($aVal, $bVal);
            } else {
                $comparison = $aVal <=> $bVal;
            }

            return $sortOrder === 'asc' ? $comparison : -$comparison;
        });

        return response()->json([
            'data' => $report,
            'total' => count($report),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Time-based analytics (hourly, daily, weekly)
     */
    public function timeSeries(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'interval' => 'nullable|string|in:hourly,daily,weekly',
            'device_id' => 'nullable|integer',
        ]);

        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : Carbon::now()->subDays(30);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : Carbon::now();
        $interval = $validated['interval'] ?? 'daily';

        $messages = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($validated['device_id'] ?? null) {
            $messages = $messages->where('device_id', $validated['device_id']);
        }

        $messages = $messages->get();

        $series = [];

        if ($interval === 'hourly') {
            $messages = $messages->groupBy(fn($m) => $m->created_at->format('Y-m-d H:00:00'));
            foreach ($messages as $time => $group) {
                $series[] = [
                    'time' => $time,
                    'count' => $group->count(),
                    'avg_qos' => number_format($group->avg('qos'), 2),
                ];
            }
        } elseif ($interval === 'daily') {
            $messages = $messages->groupBy(fn($m) => $m->created_at->format('Y-m-d'));
            foreach ($messages as $time => $group) {
                $series[] = [
                    'time' => $time,
                    'count' => $group->count(),
                    'avg_qos' => number_format($group->avg('qos'), 2),
                ];
            }
        } elseif ($interval === 'weekly') {
            $messages = $messages->groupBy(fn($m) => $m->created_at->weekOfYear);
            foreach ($messages as $week => $group) {
                $series[] = [
                    'week' => $week,
                    'count' => $group->count(),
                    'avg_qos' => number_format($group->avg('qos'), 2),
                ];
            }
        }

        usort($series, fn($a, $b) => $a['time'] ?? $a['week'] <=> $b['time'] ?? $b['week']);

        return response()->json([
            'data' => $series,
            'interval' => $interval,
            'total_messages' => collect($series)->sum('count'),
        ]);
    }
}
