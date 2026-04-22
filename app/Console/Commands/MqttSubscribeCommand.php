<?php
namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Message;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttSubscribeCommand extends Command
{
    protected $signature = 'mqtt:subscribe {--user_id= : Restrict subscription to a single user ID} {--username= : MQTT username} {--password= : MQTT password} {--device_id= : MQTT device ID to subscribe for}';
    protected $description = 'Subscribe to MQTT topics and ingest messages into the database (subtest)';

    public function handle(): int
    {
        $host = config('mqtt.host');
        $port = (int) config('mqtt.port');
        $userId = $this->option('user_id');
        $mqttUsername = $this->option('username');
        $mqttPassword = $this->option('password');
        $deviceIdOption = $this->option('device_id');

        if ($mqttUsername === null || $mqttUsername === '' || $mqttPassword === null || $mqttPassword === '' || $deviceIdOption === null || $deviceIdOption === '') {
            $this->error('The --username, --password, and --device_id options are required.');
            return self::FAILURE;
        }

        $projectsQuery = Project::where('active', true);
        if ($userId !== null && $userId !== '') {
            $projectsQuery->where('user_id', (int) $userId);
        }
        $projects = $projectsQuery->get();

        if ($projects->isEmpty()) {
            $this->warn('No active projects found for current listener scope.');
            return self::FAILURE;
        }
        $mqtt = new MqttClient($host, $port, $deviceIdOption);
        $settings = (new ConnectionSettings())
            ->setUsername((string) $mqttUsername)
            ->setPassword((string) $mqttPassword)
            ->setKeepAliveInterval(60)
            ->setUseTls(true);


        $mqtt->connect($settings, true);

        foreach ($projects as $project) {
            $user = $project->user;
            if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
                $this->warn('Project owner does not have an active subscription with advanced analytics enabled.');
                continue;
            }

            foreach ($project->topics as $topicModel) {
                if (!$topicModel->enabled || empty($topicModel->template)) {
                    continue;
                }
                // Replace {project} with project key and {device id} with +
                $topicTemplate = str_replace(['{project}', '{device_id}'], [$project->project_key, '+'], $topicModel->template);
                $mqtt->subscribe($topicTemplate, function (string $topic, string $message, bool $retained, int $qos) use ($project, $topicModel) {
                    // Log incoming message
                    Log::info('MQTT Subscriber received message', [
                        'topic' => $topic,
                        'message' => $message,
                        'qos' => $qos,
                        'retained' => $retained,
                        'project_id' => $project->id,
                        'topic_id' => $topicModel->id,
                    ]);
                    $parts = explode('/', $topic);
                    if (count($parts) < 3) {
                        return;
                    }
                    $deviceId = $parts[1];
                    $device = Device::firstOrCreate(
                        [
                            'project_id' => $project->id,
                            'device_id' => $deviceId,
                        ],
                        [
                            'type' => 'sensor',
                            'active' => true,
                        ]
                    );
                    // Message creation moved here
                    // Calculate expires_at based on user's subscription retention days
                    $limits = $project->user ? $project->user->getSubscriptionLimits() : [];
                    $retentionDays = isset($limits['data_retention_days']) ? (int)$limits['data_retention_days'] : 0;
                    $expiresAt = $retentionDays > 0 ? Carbon::now()->addDays($retentionDays) : null;
                    $message = Message::create([
                        'project_id' => $project->id,
                        'device_id' => $device->id,
                        'topic_id' => $topicModel->id,
                        'mqtt_topic' => $topic,
                        'payload' => (string) $message,
                        'qos' => $qos,
                        'retained' => $retained,
                        'expires_at' => $expiresAt,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }, 0);
                $this->info("Subscribed to {$topicTemplate}");
            }
        }
        $mqtt->loop(true);
        return self::SUCCESS;
    }
}