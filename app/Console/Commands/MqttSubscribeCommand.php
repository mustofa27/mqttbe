<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\MqttMessageIngestService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttSubscribeCommand extends Command
{
    protected $signature = 'mqtt:subscribe {--project_id=}';
    protected $description = 'Subscribe to MQTT topics and ingest messages into the database';

    public function handle(MqttMessageIngestService $ingestService): int
    {
        $host = config('mqtt.host');
        $port = (int) config('mqtt.port');
        $systemUsername = config('mqtt.username');
        $systemPassword = config('mqtt.password');
        $clientPrefix = config('mqtt.client_id_prefix', 'dashboard-subscriber');

        $projects = Project::query()
            ->where('active', true)
            ->when($this->option('project_id'), fn ($q) => $q->where('id', $this->option('project_id')))
            ->get();

        if ($projects->isEmpty()) {
            $this->warn('No active projects found.');
            return self::SUCCESS;
        }

        foreach ($projects as $project) {
            // Use sys device client_id for this project
            $clientId = 'sys_device-' . $project->id;
            $mqtt = new MqttClient($host, $port, $clientId);

            $projectPassword = $project->project_secret_plain
                ? Crypt::decryptString($project->project_secret_plain)
                : null;

            $settings = (new ConnectionSettings())
                ->setUsername($systemUsername ?: $project->project_key)
                ->setPassword($systemPassword ?: $projectPassword)
                ->setKeepAliveInterval(60);

            $mqtt->connect($settings, true);

            $topic = $project->project_key . '/#';

            $mqtt->subscribe($topic, function (string $topic, string $message, bool $retained, int $qos) use ($ingestService) {
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
                    // swallow to keep subscriber alive
                }
            }, 0);

            $this->info("Subscribed to {$topic}");

            $mqtt->loop(true);
            $mqtt->disconnect();
        }

        return self::SUCCESS;
    }
}