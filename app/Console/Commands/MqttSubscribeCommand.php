<?php
namespace App\Console\Commands;

use App\Models\Project;
use App\Services\MqttMessageIngestService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttSubscribeCommand extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT topics and ingest messages into the database (subtest)';

    public function handle(MqttMessageIngestService $ingestService): int
    {
        $host = config('mqtt.host');
        $port = (int) config('mqtt.port');
        $systemUsername = config('mqtt.username');
        $systemPassword = config('mqtt.password');
        $clientPrefix = config('mqtt.client_id_prefix', 'dashboard-subscriber');

        $projects = Project::where('active', true)
            ->get();

        if (!$project) {
            $this->warn("No active project found with id {$projectId}.");
            return self::FAILURE;
        }
        $clientId = $clientPrefix . '-' . uniqid();
        $mqtt = new MqttClient($host, $port, $clientId);
        $settings = (new ConnectionSettings())
            ->setUsername($systemUsername)
            ->setPassword($systemPassword)
            ->setKeepAliveInterval(60);
        
        $mqtt->connect($settings, true);

        foreach ($projects as $project) {
            $user = $project->user;
            if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
                $this->warn('Project owner does not have an active subscription with advanced analytics enabled.');
                return self::FAILURE;
            }

            foreach ($project->topics as $topicModel) {
                if (!$topicModel->enabled || empty($topicModel->template)) {
                    continue;
                }
                // Replace {project} with project key and {device id} with +
                $topicTemplate = str_replace(['{project}', '{device_id}'], [$project->project_key, '+'], $topicModel->template);
                $mqtt->subscribe($topicTemplate, function (string $topic, string $message, bool $retained, int $qos) use ($ingestService) {
                    $parts = explode('/', $topic);
                    if (count($parts) < 3) {
                        return;
                    }
                    $projectKey = $parts[0];
                    $deviceId = $parts[1];
                    try {
                        $ingestService->ingest(
                            $projectKey,
                            $deviceId,
                            $topic,
                            $message,
                            $qos,
                            $retained,
                            Carbon::now()
                        );
                    } catch (\Throwable $e) {
                        // Log the exception to keep subscriber alive
                        \Log::error('MQTT Ingest Exception', [
                            'project_key' => $projectKey,
                            'device_id' => $deviceId,
                            'topic' => $topic,
                            'message' => $message,
                            'qos' => $qos,
                            'retained' => $retained,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }, 0);
                $this->info("Subscribed to {$topicTemplate}");
            }
        }
        $mqtt->loop(true);
        $mqtt->disconnect();
        return self::SUCCESS;
    }
}