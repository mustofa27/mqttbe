<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Models\Project;
use App\Models\Topic;
use App\Models\Device;
use App\Services\UsageTrackingService;
use App\Services\MqttPublishService;
use App\Services\WebhookService;
use App\Services\AlertService;
use Illuminate\Http\Request;

class MessageController
{
    protected UsageTrackingService $usageService;
    protected MqttPublishService $mqttService;
    protected WebhookService $webhookService;
    protected AlertService $alertService;

    public function __construct(
        UsageTrackingService $usageService,
        MqttPublishService $mqttService,
        WebhookService $webhookService,
        AlertService $alertService
    ) {
        $this->usageService = $usageService;
        $this->mqttService = $mqttService;
        $this->webhookService = $webhookService;
        $this->alertService = $alertService;
    }

    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $topicId = $request->query('topic_id');
        $limit = min($request->query('limit', 50), 1000);
        $user = $request->user();

        if (!$projectId) {
            return response()->json(['error' => 'project_id is required'], 400);
        }

        $project = Project::findOrFail($projectId);

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Message::where('project_id', $projectId);

        if ($topicId) {
            $query->where('topic_id', $topicId);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $messages]);
    }

    public function show(Request $request, Message $message)
    {
        $user = $request->user();

        if ($message->project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $message]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $limits = $request->plan_limits;

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'device_id' => ['required', 'exists:devices,id'],
            'topic_id' => ['required', 'exists:topics,id'],
            'payload' => ['required', 'string'],
            'qos' => ['nullable', 'in:0,1,2'],
            'retained' => ['nullable', 'boolean'],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify device belongs to this project
        $device = Device::where('id', $validated['device_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        // Verify topic belongs to this project
        $topic = Topic::where('id', $validated['topic_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        // Check rate limit
        if ($this->usageService->hasExceededRateLimit($project->id)) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => "You have exceeded your hourly message limit of {$limits['rate_limit_per_hour']}",
            ], 429);
        }

        // Record usage
        $this->usageService->recordMessage($project->id);

        // Set expiration based on retention policy
        $expiresAt = null;
        if ($limits['data_retention_days'] !== -1) {
            $expiresAt = now()->addDays($limits['data_retention_days']);
        }

        // Build MQTT topic by replacing {device_id} and {project} placeholders in template
        $mqttTopic = str_replace(
            ['{device_id}', '{project}'],
            [$device->device_id, $project->project_key],
            $topic->template
        );

        // Publish to MQTT using project credentials
        $qos = (int) ($validated['qos'] ?? 0);
        $retained = (bool) ($validated['retained'] ?? false);
        $this->mqttService->publish($project, $mqttTopic, $validated['payload'], $qos, $retained);

        // Store message in database
        $message = Message::create([
            'project_id' => $validated['project_id'],
            'device_id' => $validated['device_id'],
            'topic_id' => $validated['topic_id'],
            'payload' => $validated['payload'],
            'qos' => $qos,
            'retained' => $retained,
            'mqtt_topic' => $mqttTopic,
            'expires_at' => $expiresAt,
        ]);

        // Trigger webhooks for message_published event
        $this->webhookService->trigger($project, 'message_published', [
            'event' => 'message_published',
            'timestamp' => now()->toIso8601String(),
            'project_id' => $project->id,
            'project_name' => $project->name,
            'device_id' => $device->device_id,
            'topic' => $mqttTopic,
            'payload' => $validated['payload'],
            'qos' => $qos,
            'retained' => $retained,
        ]);

        // Check and trigger alerts
        $this->alertService->checkAlerts($project);

        return response()->json([
            'data' => $message,
            'message' => 'Message published successfully',
        ], 201);
    }

    public function destroy(Request $request, Message $message)
    {
        $user = $request->user();

        if ($message->project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
