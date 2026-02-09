<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Message;
use App\Models\Project;
use App\Models\Topic;
use Carbon\Carbon;

class MqttMessageIngestService
{
    public function __construct(private UsageTrackingService $usageTracking)
    {
    }

    public function ingest(
        string $projectKey,
        string $deviceIdentifier,
        string $topicName,
        mixed $payload,
        int $qos = 0,
        bool $retained = false,
        ?Carbon $timestamp = null
    ): Message {
        $project = Project::where('project_key', $projectKey)
            ->where('active', true)
            ->firstOrFail();

        $device = Device::where('project_id', $project->id)
            ->where('device_id', $deviceIdentifier)
            ->where('active', true)
            ->firstOrFail();

        $topic = $this->resolveTopic($project, $device, $topicName);

        if (!$topic) {
            throw new \RuntimeException('Topic not allowed');
        }

        if (is_array($payload) || is_object($payload)) {
            $payload = json_encode($payload);
        }

        $createdAt = $timestamp ?? Carbon::now();

        $message = Message::create([
            'project_id' => $project->id,
            'device_id' => $device->id,
            'topic_id' => $topic->id,
            'mqtt_topic' => $topicName,
            'payload' => (string) $payload,
            'qos' => $qos,
            'retained' => $retained,
            'expires_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $this->usageTracking->recordMessage($project->id, 1);

        return $message;
    }

    private function resolveTopic(Project $project, Device $device, string $mqttTopic): ?Topic
    {
        $topics = Topic::where('project_id', $project->id)
            ->where('enabled', true)
            ->get();

        foreach ($topics as $topic) {
            $pattern = str_replace(
                ['{project}', '{device_id}'],
                [$project->project_key, $device->device_id],
                $topic->template
            );

            if ($pattern === $mqttTopic) {
                return $topic;
            }
        }

        return null;
    }
}