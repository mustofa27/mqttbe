<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class RestoreMqttListenersCommand extends Command
{
    protected $signature = 'mqtt:listeners:restore {--user_id= : Restore only one user listener} {--dry-run : Show what would run without starting processes}';

    protected $description = 'Restore per-user MQTT listener processes from stored metadata after server reboot';

    public function handle(): int
    {
        $phpBinary = $this->resolvePhpCliBinary();

        $targetUserId = $this->option('user_id');
        $dryRun = (bool) $this->option('dry-run');

        $metadataDir = storage_path('app/mqtt-listener');
        if (!File::isDirectory($metadataDir)) {
            $this->info('No listener metadata directory found. Nothing to restore.');
            return self::SUCCESS;
        }

        $files = glob($metadataDir . '/user-*.json') ?: [];
        if ($targetUserId !== null && $targetUserId !== '') {
            $target = (int) $targetUserId;
            $files = array_filter($files, function (string $path) use ($target) {
                return preg_match('/user-' . preg_quote((string) $target, '/') . '(?:-project-\d+)?\.json$/', $path) === 1;
            });
        }

        if (empty($files)) {
            $this->info('No listener metadata files matched the restore scope.');
            return self::SUCCESS;
        }

        $restored = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $file) {
            $scope = $this->extractScopeFromPath($file);
            $userId = $scope['user_id'] ?? null;
            $pathProjectId = $scope['project_id'] ?? null;
            if ($userId === null) {
                $this->warn("Skipping unrecognized metadata file: {$file}");
                $skipped++;
                continue;
            }

            $user = User::find($userId);
            if (!$user) {
                $this->warn("Skipping user {$userId}: user no longer exists.");
                $skipped++;
                continue;
            }

            if (!$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
                $this->warn("Skipping user {$userId}: no active Advance Dashboard feature.");
                $skipped++;
                continue;
            }

            $metadata = $this->readMetadata($file);
            $projectId = isset($metadata['project_id']) ? (int) $metadata['project_id'] : (int) ($pathProjectId ?? 0);
            $mqttUsername = trim((string) ($metadata['mqtt_username'] ?? ''));
            $deviceId = trim((string) ($metadata['device_id'] ?? ''));
            $mqttPassword = $this->decryptPassword($metadata['mqtt_password'] ?? null);

            if ($mqttUsername === '' || $deviceId === '' || $mqttPassword === '') {
                $this->warn("Skipping user {$userId}: missing saved MQTT credentials or device ID.");
                $skipped++;
                continue;
            }

            $runningPid = $this->findRunningPidForScope($userId, $projectId > 0 ? $projectId : null);
            if ($runningPid > 0) {
                $label = $projectId > 0 ? "User {$userId} project {$projectId}" : "User {$userId}";
                $this->line("{$label}: already running (PID {$runningPid}), skipping.");
                $this->writeMetadata($file, [
                    'pid' => $runningPid,
                    'started_at' => now()->toDateTimeString(),
                    'log_path' => $this->logPathForScope($userId, $projectId > 0 ? $projectId : null),
                ]);
                $skipped++;
                continue;
            }

            $logPath = $this->logPathForScope($userId, $projectId > 0 ? $projectId : null);
            $command = $projectId > 0
                ? sprintf(
                    'nohup %s %s mqtt:subscribe --user_id=%d --project_id=%d --username=%s --password=%s --device_id=%s >> %s 2>&1 & echo $!',
                    escapeshellarg($phpBinary),
                    escapeshellarg(base_path('artisan')),
                    $userId,
                    $projectId,
                    escapeshellarg($mqttUsername),
                    escapeshellarg($mqttPassword),
                    escapeshellarg($deviceId),
                    escapeshellarg($logPath)
                )
                : sprintf(
                    'nohup %s %s mqtt:subscribe --user_id=%d --username=%s --password=%s --device_id=%s >> %s 2>&1 & echo $!',
                    escapeshellarg($phpBinary),
                    escapeshellarg(base_path('artisan')),
                    $userId,
                    escapeshellarg($mqttUsername),
                    escapeshellarg($mqttPassword),
                    escapeshellarg($deviceId),
                    escapeshellarg($logPath)
                );

            if ($dryRun) {
                $this->line("[dry-run] User {$userId}: {$command}");
                $restored++;
                continue;
            }

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(20);
            $process->run();

            $pid = (int) trim($process->getOutput());
            if ($process->isSuccessful() && $pid > 0) {
                $this->writeMetadata($file, [
                    'pid' => $pid,
                    'started_at' => now()->toDateTimeString(),
                    'log_path' => $logPath,
                ]);
                $label = $projectId > 0 ? "User {$userId} project {$projectId}" : "User {$userId}";
                $this->info("{$label}: restored listener (PID {$pid}).");
                $restored++;
            } else {
                $message = trim($process->getErrorOutput() ?: $process->getOutput());
                $label = $projectId > 0 ? "User {$userId} project {$projectId}" : "User {$userId}";
                $this->error("{$label}: failed to restore listener. {$message}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Restore summary => restored: {$restored}, skipped: {$skipped}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function extractScopeFromPath(string $path): array
    {
        if (preg_match('/user-(\d+)(?:-project-(\d+))?\.json$/', $path, $matches) !== 1) {
            return [
                'user_id' => null,
                'project_id' => null,
            ];
        }

        return [
            'user_id' => (int) $matches[1],
            'project_id' => isset($matches[2]) ? (int) $matches[2] : null,
        ];
    }

    private function findRunningPidForScope(int $userId, ?int $projectId): int
    {
        $command = $projectId !== null
            ? 'pgrep -f "artisan mqtt:subscribe --user_id=' . $userId . ' --project_id=' . $projectId . '" | head -n 1'
            : 'pgrep -f "artisan mqtt:subscribe --user_id=' . $userId . '" | head -n 1';
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(5);
        $process->run();

        if (!$process->isSuccessful()) {
            return 0;
        }

        return (int) trim($process->getOutput());
    }

    private function writeMetadata(string $path, array $metadata): void
    {
        File::ensureDirectoryExists(dirname($path));
        $current = File::exists($path) ? $this->readMetadata($path) : [];

        File::put($path, json_encode(array_merge($current, $metadata), JSON_PRETTY_PRINT));
    }

    private function logPathForScope(int $userId, ?int $projectId): string
    {
        if ($projectId !== null && $projectId > 0) {
            return storage_path("logs/mqtt-subscriber-user-{$userId}-project-{$projectId}.log");
        }

        return storage_path("logs/mqtt-subscriber-user-{$userId}.log");
    }

    private function readMetadata(string $path): array
    {
        $decoded = json_decode((string) File::get($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function decryptPassword(mixed $encryptedPassword): string
    {
        if (!is_string($encryptedPassword) || $encryptedPassword === '') {
            return '';
        }

        try {
            return Crypt::decryptString($encryptedPassword);
        } catch (\Throwable) {
            return '';
        }
    }

    private function resolvePhpCliBinary(): string
    {
        $binary = PHP_BINARY;
        $baseName = strtolower(basename($binary));

        if (str_contains($baseName, 'php-fpm')) {
            $finder = Process::fromShellCommandline('command -v php');
            $finder->setTimeout(3);
            $finder->run();

            if ($finder->isSuccessful()) {
                $resolved = trim((string) $finder->getOutput());
                if ($resolved !== '') {
                    return $resolved;
                }
            }

            return 'php';
        }

        return $binary;
    }
}
