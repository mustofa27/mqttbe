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
        $targetUserId = $this->option('user_id');
        $dryRun = (bool) $this->option('dry-run');

        $metadataDir = storage_path('app/mqtt-listener');
        if (!File::isDirectory($metadataDir)) {
            $this->info('No listener metadata directory found. Nothing to restore.');
            return self::SUCCESS;
        }

        $files = glob($metadataDir . '/user-*.json') ?: [];
        if ($targetUserId !== null && $targetUserId !== '') {
            $files = array_filter($files, function (string $path) use ($targetUserId) {
                return str_contains($path, 'user-' . (int) $targetUserId . '.json');
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
            $userId = $this->extractUserIdFromPath($file);
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
                $this->warn("Skipping user {$userId}: no active advanced analytics feature.");
                $skipped++;
                continue;
            }

            $metadata = $this->readMetadata($file);
            $mqttUsername = trim((string) ($metadata['mqtt_username'] ?? ''));
            $deviceId = trim((string) ($metadata['device_id'] ?? ''));
            $mqttPassword = $this->decryptPassword($metadata['mqtt_password'] ?? null);

            if ($mqttUsername === '' || $deviceId === '' || $mqttPassword === '') {
                $this->warn("Skipping user {$userId}: missing saved MQTT credentials or device ID.");
                $skipped++;
                continue;
            }

            $runningPid = $this->findRunningPidForUser($userId);
            if ($runningPid > 0) {
                $this->line("User {$userId}: already running (PID {$runningPid}), skipping.");
                $this->writeMetadata($userId, [
                    'pid' => $runningPid,
                    'started_at' => now()->toDateTimeString(),
                    'log_path' => $this->logPathForUser($userId),
                ]);
                $skipped++;
                continue;
            }

            $logPath = $this->logPathForUser($userId);
            $command = sprintf(
                'nohup %s %s mqtt:subscribe --user_id=%d --username=%s --password=%s --device_id=%s >> %s 2>&1 & echo $!',
                escapeshellarg(PHP_BINARY),
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
                $this->writeMetadata($userId, [
                    'pid' => $pid,
                    'started_at' => now()->toDateTimeString(),
                    'log_path' => $logPath,
                ]);
                $this->info("User {$userId}: restored listener (PID {$pid}).");
                $restored++;
            } else {
                $message = trim($process->getErrorOutput() ?: $process->getOutput());
                $this->error("User {$userId}: failed to restore listener. {$message}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Restore summary => restored: {$restored}, skipped: {$skipped}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function extractUserIdFromPath(string $path): ?int
    {
        if (preg_match('/user-(\d+)\.json$/', $path, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    private function findRunningPidForUser(int $userId): int
    {
        $command = 'pgrep -f "artisan mqtt:subscribe --user_id=' . $userId . '" | head -n 1';
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(5);
        $process->run();

        if (!$process->isSuccessful()) {
            return 0;
        }

        return (int) trim($process->getOutput());
    }

    private function writeMetadata(int $userId, array $metadata): void
    {
        $path = storage_path("app/mqtt-listener/user-{$userId}.json");
        File::ensureDirectoryExists(dirname($path));
        $current = File::exists($path) ? $this->readMetadata($path) : [];

        File::put($path, json_encode(array_merge($current, $metadata), JSON_PRETTY_PRINT));
    }

    private function logPathForUser(int $userId): string
    {
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
}
