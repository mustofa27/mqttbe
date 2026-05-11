<?php

namespace App\Http\Controllers;

use App\Models\AdvanceDashboardWidget;
use App\Models\Device;
use App\Models\Message;
use App\Models\Project;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        $projects = $user->projects()
            ->with(['topics' => function ($query) {
                $query->where('enabled', true)->orderBy('code');
            }])
            ->orderBy('name')
            ->get();

        $widgets = AdvanceDashboardWidget::where('user_id', (int) $user->id)
            ->with(['project:id,name', 'topic:id,code'])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        // Attach distinct device codes that have sent messages on each widget's topic.
        $widgets->each(function (AdvanceDashboardWidget $widget) {
            $devicePks = Message::where('project_id', (int) $widget->project_id)
                ->where('topic_id', (int) $widget->topic_id)
                ->distinct()
                ->pluck('device_id')
                ->filter()
                ->all();

            $widget->setAttribute('available_devices', Device::whereIn('id', $devicePks)
                ->orderBy('device_id')
                ->pluck('device_id')
                ->all());
        });

        $projectTopics = $projects->mapWithKeys(function ($project) {
            return [
                (string) $project->id => $project->topics->map(function ($topic) {
                    return [
                        'id' => (int) $topic->id,
                        'label' => (string) ($topic->code ?: $topic->template),
                    ];
                })->values()->all(),
            ];
        })->all();

        return view('dashboard.advance-dashboard.index', compact('projects', 'widgets', 'projectTopics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        $limits = $user->getSubscriptionLimits();
        $maxWidgets = (int) ($limits['max_advance_dashboard_widgets'] ?? 0);
        $currentWidgets = AdvanceDashboardWidget::where('user_id', (int) $user->id)->count();

        if ($maxWidgets !== -1 && $currentWidgets >= $maxWidgets) {
            return back()->withErrors([
                'limit' => "Widget limit reached for your plan ({$maxWidgets} widgets).",
            ])->withInput();
        }

        $validated = $request->validate([
            'project_id' => ['required', 'integer'],
            'topic_id' => ['required', 'integer'],
            'title' => ['nullable', 'string', 'max:120'],
            'data_type' => ['required', 'in:number,text,json'],
            'visualization_mode' => ['required', 'in:time_series,bar'],
            'json_key' => ['nullable', 'string', 'max:120'],
            'json_key_type' => ['nullable', 'in:number,text'],
        ]);

        $project = Project::where('id', (int) $validated['project_id'])
            ->where('user_id', (int) $user->id)
            ->first();

        if (!$project) {
            return back()->withErrors(['project_id' => 'Selected project is invalid.'])->withInput();
        }

        $topic = Topic::where('id', (int) $validated['topic_id'])
            ->where('project_id', (int) $project->id)
            ->where('enabled', true)
            ->first();

        if (!$topic) {
            return back()->withErrors(['topic_id' => 'Selected topic is invalid for this project.'])->withInput();
        }

        if ($validated['data_type'] === 'json') {
            if (empty($validated['json_key'])) {
                return back()->withErrors(['json_key' => 'JSON key is required for JSON data type.'])->withInput();
            }

            if (empty($validated['json_key_type'])) {
                return back()->withErrors(['json_key_type' => 'JSON key datatype is required for JSON data type.'])->withInput();
            }
        } else {
            $validated['json_key'] = null;
            $validated['json_key_type'] = null;
        }

        $maxPosition = (int) AdvanceDashboardWidget::where('user_id', (int) $user->id)->max('position');
        $defaultTitle = ucfirst($validated['data_type']) . ' - ' . ($topic->code ?: $topic->template);

        AdvanceDashboardWidget::create([
            'user_id' => (int) $user->id,
            'project_id' => (int) $project->id,
            'topic_id' => (int) $topic->id,
            'title' => trim((string) ($validated['title'] ?? '')) !== '' ? trim((string) $validated['title']) : $defaultTitle,
            'data_type' => (string) $validated['data_type'],
            'visualization_mode' => (string) $validated['visualization_mode'],
            'json_key' => $validated['json_key'] ?? null,
            'json_key_type' => $validated['json_key_type'] ?? null,
            'size' => 'medium',
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('advance-dashboard.index')->with('success', 'Chart added to Advance Dashboard.');
    }

    public function destroy(Request $request, AdvanceDashboardWidget $widget): RedirectResponse
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        if ((int) $widget->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized widget access.');
        }

        $widget->delete();

        return redirect()->route('advance-dashboard.index')->with('success', 'Chart removed from Advance Dashboard.');
    }

    public function data(Request $request, AdvanceDashboardWidget $widget): JsonResponse
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        if ((int) $widget->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Unauthorized widget access.'], 403);
        }

        $deviceCode = $request->query('device_code');

        $messagesQuery = Message::where('project_id', (int) $widget->project_id)
            ->where('topic_id', (int) $widget->topic_id);

        if ($deviceCode !== null && $deviceCode !== '') {
            $devicePk = Device::where('project_id', (int) $widget->project_id)
                ->where('device_id', (string) $deviceCode)
                ->value('id');

            if ($devicePk !== null) {
                $messagesQuery->where('device_id', (int) $devicePk);
            } else {
                // No matching device — return empty
                return response()->json(['empty' => true, 'message' => 'No messages found for this device.']);
            }
        }

        $messages = $messagesQuery->orderBy('created_at')
            ->limit(500)
            ->get(['payload', 'created_at', 'device_id']);

        if ($messages->isEmpty()) {
            return response()->json([
                'empty' => true,
                'message' => 'No messages found for this topic.',
            ]);
        }

        $points = [];
        foreach ($messages as $message) {
            $value = $this->extractTypedValue($widget, (string) $message->payload);
            if ($value === null) {
                continue;
            }

            $points[] = [
                'x' => Carbon::parse($message->created_at)->format('Y-m-d H:i:s'),
                'hour' => Carbon::parse($message->created_at)->format('Y-m-d H:00'),
                'value' => $value,
            ];
        }

        if (empty($points)) {
            return response()->json([
                'empty' => true,
                'message' => 'No plottable data found for this chart configuration.',
            ]);
        }

        $isNumeric = $widget->data_type === 'number' || ($widget->data_type === 'json' && $widget->json_key_type === 'number');
        $datasetLabel = $widget->title ?: 'Advance Dashboard Chart';

        if ($isNumeric) {
            if ($widget->visualization_mode === 'time_series') {
                return response()->json([
                    'type' => 'line',
                    'labels' => array_column($points, 'x'),
                    'datasets' => [[
                        'label' => $datasetLabel,
                        'data' => array_column($points, 'value'),
                        'borderColor' => '#0d6efd',
                        'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                ]);
            }

            $bucketTotals = [];
            $bucketCounts = [];
            foreach ($points as $point) {
                $hour = $point['hour'];
                $bucketTotals[$hour] = ($bucketTotals[$hour] ?? 0) + (float) $point['value'];
                $bucketCounts[$hour] = ($bucketCounts[$hour] ?? 0) + 1;
            }

            $labels = array_keys($bucketTotals);
            $data = array_map(function ($hour) use ($bucketTotals, $bucketCounts) {
                return round($bucketTotals[$hour] / max(1, $bucketCounts[$hour]), 4);
            }, $labels);

            return response()->json([
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [[
                    'label' => $datasetLabel . ' (avg/hour)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(25, 135, 84, 0.35)',
                    'borderColor' => '#198754',
                    'borderWidth' => 1,
                ]],
            ]);
        }

        if ($widget->visualization_mode === 'time_series') {
            $bucketCounts = [];
            foreach ($points as $point) {
                $hour = $point['hour'];
                $bucketCounts[$hour] = ($bucketCounts[$hour] ?? 0) + 1;
            }

            return response()->json([
                'type' => 'line',
                'labels' => array_keys($bucketCounts),
                'datasets' => [[
                    'label' => $datasetLabel . ' (entries/hour)',
                    'data' => array_values($bucketCounts),
                    'borderColor' => '#fd7e14',
                    'backgroundColor' => 'rgba(253, 126, 20, 0.2)',
                    'fill' => true,
                    'tension' => 0.25,
                ]],
            ]);
        }

        $frequencies = [];
        foreach ($points as $point) {
            $key = (string) $point['value'];
            $frequencies[$key] = ($frequencies[$key] ?? 0) + 1;
        }
        arsort($frequencies);
        $frequencies = array_slice($frequencies, 0, 20, true);

        return response()->json([
            'type' => 'bar',
            'labels' => array_keys($frequencies),
            'datasets' => [[
                'label' => $datasetLabel . ' (frequency)',
                'data' => array_values($frequencies),
                'backgroundColor' => 'rgba(111, 66, 193, 0.35)',
                'borderColor' => '#6f42c1',
                'borderWidth' => 1,
            ]],
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        $validated = $request->validate([
            'widget_ids' => ['required', 'array', 'min:1'],
            'widget_ids.*' => ['integer'],
        ]);

        $widgetIds = collect($validated['widget_ids'])
            ->map(fn($id) => (int) $id)
            ->values();

        $ownedCount = AdvanceDashboardWidget::where('user_id', (int) $user->id)
            ->whereIn('id', $widgetIds)
            ->count();

        if ($ownedCount !== $widgetIds->count()) {
            return response()->json(['message' => 'Invalid widget list.'], 422);
        }

        DB::transaction(function () use ($user, $widgetIds) {
            foreach ($widgetIds as $index => $widgetId) {
                AdvanceDashboardWidget::where('id', $widgetId)
                    ->where('user_id', (int) $user->id)
                    ->update(['position' => $index + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function updateSize(Request $request, AdvanceDashboardWidget $widget): JsonResponse
    {
        $user = $this->requireAdvanceDashboardAccess($request);

        if ((int) $widget->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Unauthorized widget access.'], 403);
        }

        $validated = $request->validate([
            'size' => ['required', 'in:small,medium,wide'],
        ]);

        $widget->size = (string) $validated['size'];
        $widget->save();

        return response()->json(['ok' => true, 'size' => $widget->size]);
    }

    private function requireAdvanceDashboardAccess(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
            abort(403, 'Advanced Dashboard access is required.');
        }

        return $user;
    }

    private function extractTypedValue(AdvanceDashboardWidget $widget, string $payload): float|string|null
    {
        if ($widget->data_type === 'number') {
            $value = trim($payload);
            return is_numeric($value) ? (float) $value : null;
        }

        if ($widget->data_type === 'text') {
            $value = trim($payload);
            return $value === '' ? null : $value;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        $jsonKey = (string) ($widget->json_key ?? '');
        if ($jsonKey === '' || !array_key_exists($jsonKey, $decoded)) {
            return null;
        }

        $rawValue = $decoded[$jsonKey];

        if ($widget->json_key_type === 'number') {
            return is_numeric($rawValue) ? (float) $rawValue : null;
        }

        if (is_scalar($rawValue)) {
            $value = trim((string) $rawValue);
            return $value === '' ? null : $value;
        }

        return null;
    }
}
