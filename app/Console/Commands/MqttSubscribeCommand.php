<?php
namespace App\Console\Commands;

use App\Models\Project;
use App\Services\MqttMessageIngestService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use PhpMqtt\Client\MqttClient;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;

class MqttSubscribeCommand extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT topics and ingest messages into the database (subtest)';

    public function handle(): int
    {
        $host = config('mqtt.host');
        $port = (int) config('mqtt.port');
        $systemUsername = config('mqtt.username');
        $systemPassword = config('mqtt.password');
        $clientPrefix = config('mqtt.client_id_prefix', 'dashboard-subscriber');
        $caFile = config('mqtt.cafile', '/etc/mosquitto/ca_certificates/ca.crt');

        $projects = Project::where('active', true)
            ->get();

        if ($projects->isEmpty()) {
            $this->warn("No active projects found.");
            return self::FAILURE;
        }
        $clientId = $clientPrefix . '-' . uniqid();
        $mqtt = new MqttClient($host, $port, $clientId);
        $settings = (new ConnectionSettings())
            ->setUsername($systemUsername)
            ->setPassword($systemPassword)
            ->setKeepAliveInterval(60)
            ->setUseTls(true);
            // ->setTlsOptions([
            //     'cafile' => $caFile,
            //     // 'verify_peer' => true, // optional, recommended for production
            //     // 'verify_peer_name' => true, // optional, recommended for production
            // ]);
        
        $mqtt->connect($settings, true);

        foreach ($projects as $project) {
            $user = $project->user;
            if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
                $this->warn('Project owner does not have an active subscription with advanced analytics enabled.');
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
                    $device = Device::where('project_id', $project->id)
                        ->where('device_id', $deviceId)
                        ->where('active', true)
                        ->firstOrFail();
                    // Message creation moved here
                    $message = Message::create([
                        'project_id' => $project->id,
                        'device_id' => $device->id,
                        'topic_id' => $topicModel->id,
                        'mqtt_topic' => $topic,
                        'payload' => (string) $message,
                        'qos' => $qos,
                        'retained' => $retained,
                        'expires_at' => null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }, 0);
                $this->info("Subscribed to {$topicTemplate}");
            }
        }
        $mqtt->loop(true);
        $mqtt->disconnect();
        return self::SUCCESS;
    }
}