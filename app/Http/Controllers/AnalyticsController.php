<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Message;
use App\Models\Device;
use App\Services\UsageTrackingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Show main analytics dashboard
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $projects = $user->projects;

        return view('dashboard.analytics.dashboard', compact('projects'));
    }

    /**
     * Get analytics data for a project
     */
    public function projectData(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : Carbon::now()->subDays(30);
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : Carbon::now();

        return response()->json([
            'volume_chart' => $this->getVolumeChartData($project, $from, $to),
            'device_distribution' => $this->getDeviceDistribution($project, $from, $to),
            'topic_usage' => $this->getTopicUsage($project, $from, $to),
            'hourly_rate' => $this->getHourlyRate($project),
            'growth_trend' => $this->getGrowthTrend($project, $from, $to),
            'top_devices' => $this->getTopDevices($project, $from, $to),
            'summary' => $this->getSummary($project, $from, $to),
        ]);
    }

    /**
     * Get message volume over time (last 30 days)
     */
    private function getVolumeChartData(Project $project, Carbon $from, Carbon $to): array
    {
        $messages = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn($m) => $m->created_at->format('Y-m-d'))
            ->map(fn($group) => $group->count())
            ->all();

        // Fill missing dates with 0
        $period = \CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->endOfDay());
        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $data[] = $messages[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Messages Published',
                    'data' => $data,
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get device distribution
     */
    private function getDeviceDistribution(Project $project, Carbon $from, Carbon $to): array
    {
        $distribution = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('device:id,device_id')
            ->get()
            ->groupBy('device_id')
            ->map(fn($group) => $group->count())
            ->all();

        $devices = Device::where('project_id', $project->id)
            ->whereIn('id', array_keys($distribution))
            ->pluck('device_id', 'id')
            ->all();

        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        foreach ($distribution as $deviceId => $count) {
            $deviceName = $devices[$deviceId] ?? "Unknown";
            $labels[] = $deviceName;
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    /**
     * Get topic usage
     */
    private function getTopicUsage(Project $project, Carbon $from, Carbon $to): array
    {
        $usage = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('topic:id,template')
            ->get()
            ->groupBy('topic_id')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(10)
            ->all();

        $topics = \App\Models\Topic::where('project_id', $project->id)
            ->whereIn('id', array_keys($usage))
            ->pluck('template', 'id')
            ->all();

        $labels = [];
        $data = [];

        foreach ($usage as $topicId => $count) {
            $labels[] = $topics[$topicId] ?? "Unknown";
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Messages per Topic',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#0d6efd',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    /**
     * Get current hourly message rate
     */
    private function getHourlyRate(Project $project): array
    {
        $now = Carbon::now();
        $hourAgo = $now->copy()->subHour();

        $hourlyMessages = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$hourAgo, $now])
            ->get()
            ->groupBy(fn($m) => $m->created_at->format('H:i'))
            ->map(fn($group) => $group->count())
            ->all();

        // Fill last 60 minutes
        $labels = [];
        $data = [];
        for ($i = 59; $i >= 0; $i--) {
            $time = $now->copy()->subMinutes($i);
            $key = $time->format('H:i');
            $labels[] = $key;
            $data[] = $hourlyMessages[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Messages/Minute',
                    'data' => $data,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get growth trend (weekly comparison)
     */
    private function getGrowthTrend(Project $project, Carbon $from, Carbon $to): array
    {
        $weeks = [];
        $current = $from->copy()->startOfWeek();

        while ($current <= $to) {
            $weekEnd = $current->copy()->endOfWeek();
            $count = Message::where('project_id', $project->id)
                ->whereBetween('created_at', [$current, $weekEnd])
                ->count();

            $weeks[$current->format('Y-W')] = $count;
            $current->addWeek();
        }

        $labels = array_map(fn($key) => "Week " . substr($key, -2), array_keys($weeks));
        $data = array_values($weeks);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Weekly Message Count',
                    'data' => $data,
                    'backgroundColor' => '#ff9f40',
                    'borderColor' => '#ff7722',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    /**
     * Get top devices
     */
    private function getTopDevices(Project $project, Carbon $from, Carbon $to): array
    {
        return Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('device:id,device_id')
            ->get()
            ->groupBy('device_id')
            ->map(fn($group) => [
                'device_id' => $group->first()->device->device_id ?? 'Unknown',
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Get summary statistics
     */
    private function getSummary(Project $project, Carbon $from, Carbon $to): array
    {
        $totalMessages = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $uniqueDevices = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->distinct('device_id')
            ->count('device_id');

        $uniqueTopics = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->distinct('topic_id')
            ->count('topic_id');

        $avgMessageSize = Message::where('project_id', $project->id)
            ->whereBetween('created_at', [$from, $to])
            ->average(DB::raw('CHAR_LENGTH(payload)'));

        $qos1Messages = Message::where('project_id', $project->id)
            ->where('qos', '>=', 1)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $retainedMessages = Message::where('project_id', $project->id)
            ->where('retained', true)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        return [
            'total_messages' => $totalMessages,
            'unique_devices' => $uniqueDevices,
            'unique_topics' => $uniqueTopics,
            'avg_message_size' => round($avgMessageSize ?? 0, 2),
            'qos1_messages' => $qos1Messages,
            'retained_messages' => $retainedMessages,
            'period' => "{$from->format('M d')} - {$to->format('M d')}",
        ];
    }

    /**
     * Device-specific analytics
     */
    public function deviceAnalytics(Request $request, Project $project, Device $device)
    {
        if ($project->user_id !== auth()->id() || $device->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : Carbon::now()->subDays(7);
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : Carbon::now();

        return response()->json([
            'device_id' => $device->device_id,
            'device_type' => $device->type,
            'activity' => $this->getDeviceActivity($device, $from, $to),
            'topics' => $this->getDeviceTopics($device, $from, $to),
            'qos_distribution' => $this->getDeviceQosDistribution($device, $from, $to),
            'summary' => $this->getDeviceSummary($device, $from, $to),
        ]);
    }

    /**
     * Get device activity over time
     */
    private function getDeviceActivity(Device $device, Carbon $from, Carbon $to): array
    {
        $activity = Message::where('device_id', $device->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn($m) => $m->created_at->format('Y-m-d'))
            ->map(fn($group) => $group->count())
            ->all();

        $period = \CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->endOfDay());
        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $data[] = $activity[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => "{$device->device_id} Activity",
                    'data' => $data,
                    'borderColor' => '#9966FF',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get device topics
     */
    private function getDeviceTopics(Device $device, Carbon $from, Carbon $to): array
    {
        $topics = Message::where('device_id', $device->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('topic:id,template')
            ->get()
            ->groupBy('topic_id')
            ->map(fn($group) => $group->count())
            ->all();

        $topicModels = \App\Models\Topic::whereIn('id', array_keys($topics))->pluck('template', 'id')->all();

        $labels = [];
        $data = [];
        foreach ($topics as $topicId => $count) {
            $labels[] = $topicModels[$topicId] ?? 'Unknown';
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Messages',
                    'data' => $data,
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#0d6efd',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    /**
     * Get device QoS distribution
     */
    private function getDeviceQosDistribution(Device $device, Carbon $from, Carbon $to): array
    {
        $qos = Message::where('device_id', $device->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy('qos')
            ->map(fn($group) => $group->count())
            ->all();

        return [
            'labels' => ['QoS 0 (At Most Once)', 'QoS 1 (At Least Once)', 'QoS 2 (Exactly Once)'],
            'datasets' => [
                [
                    'data' => [$qos[0] ?? 0, $qos[1] ?? 0, $qos[2] ?? 0],
                    'backgroundColor' => ['#36A2EB', '#FFCE56', '#FF6384'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    /**
     * Get device summary
     */
    private function getDeviceSummary(Device $device, Carbon $from, Carbon $to): array
    {
        $messages = Message::where('device_id', $device->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        return [
            'total_messages' => $messages->count(),
            'unique_topics' => $messages->pluck('topic_id')->unique()->count(),
            'avg_message_size' => round($messages->average(fn($m) => strlen($m->payload)) ?? 0, 2),
            'retained_count' => $messages->where('retained', true)->count(),
            'last_activity' => Message::where('device_id', $device->id)->latest('created_at')->first()?->created_at?->diffForHumans(),
        ];
    }
}
