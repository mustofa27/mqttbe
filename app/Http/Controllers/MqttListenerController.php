<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MqttListenerController extends Controller
{
    public function adminOverview(Request $request): JsonResponse|Response
    {
        if (!$request->expectsJson() && !$request->boolean('json')) {
            return response()->view('admin.mqtt-listeners.index');
        }

        $requestedUserId = $request->query('user_id');

        $usersQuery = User::query()->select(['id', 'name', 'email', 'subscription_tier', 'subscription_active']);

        if ($requestedUserId !== null && $requestedUserId !== '') {
            $usersQuery->where('id', (int) $requestedUserId);
        }

        $users = $usersQuery->orderBy('id')->get();
        $maxPerUser = max(1, (int) config('mqtt.listener.max_processes_per_user', 1));

        $data = $users->map(function (User $user) use ($maxPerUser) {
            $service = $this->resolveStatus((int) $user->id);
            $runningCount = $this->countRunningProcessesForUser((int) $user->id);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'subscription_tier' => $user->subscription_tier,
                    'subscription_active' => (bool) $user->subscription_active,
                ],
                'service' => $service,
                'running_count' => $runningCount,
                'limit' => $maxPerUser,
                'limit_reached' => $runningCount >= $maxPerUser,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'total' => $data->count(),
            'filters' => [
                'user_id' => $requestedUserId !== null && $requestedUserId !== '' ? (int) $requestedUserId : null,
            ],
            'data' => $data,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        if ($denied = $this->denyIfNoAdvancedAnalytics($request)) {
            return $denied;
        }

        return response()->json($this->resolveStatus($request->user()->id));
    }

    public function start(Request $request): JsonResponse
    {
        if ($denied = $this->denyIfNoAdvancedAnalytics($request)) {
            return $denied;
        }

        $userId = (int) $request->user()->id;
        $existingMetadata = $this->readListenerMetadata($userId, true);
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'device_id' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $mqttUsername = trim($validated['username']);
        $deviceId = trim($validated['device_id']);
        $mqttPassword = isset($validated['password']) ? trim((string) $validated['password']) : '';

        if ($mqttPassword === '') {
            $mqttPassword = (string) ($existingMetadata['mqtt_password'] ?? '');
        }

        if ($mqttPassword === '') {
            return response()->json([
                'ok' => false,
                'action' => 'start',
                'message' => 'MQTT password is required before the listener can start.',
                'service' => $this->resolveStatus($userId),
            ], 422);
        }

        $lockTimeout = max(1, (int) config('mqtt.listener.start_lock_seconds', 10));
        $lockHandle = $this->acquireStartLock($userId, $lockTimeout);

        if ($lockHandle === false) {
            return response()->json([
                'ok' => false,
                'action' => 'start',
                'message' => 'Another start request is currently in progress. Please retry in a moment.',
                'service' => $this->resolveStatus($userId),
            ], 429);
        }

        try {
            $currentStatus = $this->resolveStatus($userId);

            if ($currentStatus['running']) {
                return response()->json([
                    'ok' => true,
                    'action' => 'start',
                    'message' => 'Listener is already running for this user.',
                    'service' => $currentStatus,
                ]);
            }

            $maxPerUser = max(1, (int) config('mqtt.listener.max_processes_per_user', 1));
            $runningCount = $this->countRunningProcessesForUser($userId);

            if ($runningCount >= $maxPerUser) {
                return response()->json([
                    'ok' => false,
                    'action' => 'start',
                    'message' => 'Process limit reached for this user.',
                    'service' => $this->resolveStatus($userId),
                    'limit' => $maxPerUser,
                    'running_count' => $runningCount,
                ], 429);
            }

            $logPath = storage_path("logs/mqtt-subscriber-user-{$userId}.log");
            $command = $this->buildStartCommand($userId, $mqttUsername, $mqttPassword, $deviceId, $logPath);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(15);
            $process->run();

            $pid = (int) trim($process->getOutput());

            if ($process->isSuccessful() && $pid > 0) {
                $this->writeListenerMetadata($userId, [
                    'pid' => $pid,
                    'started_at' => now()->toDateTimeString(),
                    'log_path' => $logPath,
                    'mqtt_username' => $mqttUsername,
                    'mqtt_password' => Crypt::encryptString($mqttPassword),
                    'device_id' => $deviceId,
                ]);
            }

            $status = $this->resolveStatus($userId);

            return response()->json([
                'ok' => $process->isSuccessful() && $pid > 0,
                'action' => 'start',
                'message' => trim($process->getOutput() ?: $process->getErrorOutput()) ?: 'Listener start requested.',
                'service' => $status,
            ], ($process->isSuccessful() && $pid > 0) ? 200 : 500);
        } finally {
            $this->releaseStartLock($lockHandle);
        }
    }

    public function stop(Request $request): JsonResponse
    {
        if ($denied = $this->denyIfNoAdvancedAnalytics($request)) {
            return $denied;
        }

        $userId = (int) $request->user()->id;
        $meta = $this->readListenerMetadata($userId);
        $pid = isset($meta['pid']) ? (int) $meta['pid'] : 0;

        if ($pid <= 0 || !$this->isProcessRunning($pid)) {
            $this->deletePidMetadata($userId);

            return response()->json([
                'ok' => true,
                'action' => 'stop',
                'message' => 'No running listener found for this user.',
                'service' => $this->resolveStatus($userId),
            ]);
        }

        $stopped = $this->stopProcess($pid);

        if ($stopped) {
            $this->deletePidMetadata($userId);
        }

        return response()->json([
            'ok' => $stopped,
            'action' => 'stop',
            'message' => $stopped ? 'Listener stopped.' : 'Failed to stop listener process.',
            'service' => $this->resolveStatus($userId),
        ], $stopped ? 200 : 500);
    }

    public function restart(Request $request): JsonResponse
    {
        if ($denied = $this->denyIfNoAdvancedAnalytics($request)) {
            return $denied;
        }

        $userId = (int) $request->user()->id;
        $meta = $this->readListenerMetadata($userId);
        $pid = isset($meta['pid']) ? (int) $meta['pid'] : 0;

        if ($pid > 0 && $this->isProcessRunning($pid)) {
            $this->stopProcess($pid);
            $this->deletePidMetadata($userId);
        }

        $startResponse = $this->start($request);
        $payload = $startResponse->getData(true);
        $payload['action'] = 'restart';

        return response()->json($payload, $startResponse->getStatusCode());
    }

    private function denyIfNoAdvancedAnalytics(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasActiveSubscription() || !$user->hasFeature('advanced_analytics_enabled')) {
            return response()->json([
                'ok' => false,
                'message' => 'Advanced analytics feature is required to manage MQTT listener.',
            ], 403);
        }

        return null;
    }

    private function resolveStatus(int $userId): array
    {
        $meta = $this->readListenerMetadata($userId, true);
        $pid = isset($meta['pid']) ? (int) $meta['pid'] : 0;
        $running = $pid > 0 && $this->isProcessRunning($pid);

        if (!$running && $pid > 0) {
            $this->writeListenerMetadata($userId, array_merge($meta, [
                'pid' => 0,
                'started_at' => null,
            ]));
            $meta = $this->readListenerMetadata($userId, true);
            $pid = 0;
        }

        return [
            'program' => 'mqtt:subscribe --user_id=' . $userId,
            'user_id' => $userId,
            'pid' => $pid,
            'running' => $running,
            'state' => $running ? 'RUNNING' : 'STOPPED',
            'raw' => $running ? ('PID ' . $pid . ' active') : 'No active process',
            'started_at' => $meta['started_at'] ?? null,
            'log_path' => $meta['log_path'] ?? storage_path("logs/mqtt-subscriber-user-{$userId}.log"),
            'mqtt_username' => $meta['mqtt_username'] ?? null,
            'device_id' => $meta['device_id'] ?? null,
            'has_password' => !empty($meta['mqtt_password']),
        ];
    }

    private function pidMetadataPath(int $userId): string
    {
        return storage_path("app/mqtt-listener/user-{$userId}.json");
    }

    private function readListenerMetadata(int $userId, bool $decryptSecrets = false): array
    {
        $path = $this->pidMetadataPath($userId);
        if (!File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);
        if (!is_array($decoded)) {
            return [];
        }

        if ($decryptSecrets && !empty($decoded['mqtt_password'])) {
            try {
                $decoded['mqtt_password'] = Crypt::decryptString((string) $decoded['mqtt_password']);
            } catch (\Throwable) {
                $decoded['mqtt_password'] = null;
            }
        }

        return $decoded;
    }

    private function writeListenerMetadata(int $userId, array $metadata): void
    {
        $path = $this->pidMetadataPath($userId);
        File::ensureDirectoryExists(dirname($path));
        $current = $this->readListenerMetadata($userId);
        File::put($path, json_encode(array_merge($current, $metadata), JSON_PRETTY_PRINT));
    }

    private function deletePidMetadata(int $userId): void
    {
        $path = $this->pidMetadataPath($userId);
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    private function isProcessRunning(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        $check = Process::fromShellCommandline('kill -0 ' . (int) $pid);
        $check->run();
        return $check->isSuccessful();
    }

    private function stopProcess(int $pid): bool
    {
        if ($pid <= 0) {
            return true;
        }

        if (function_exists('posix_kill')) {
            @posix_kill($pid, 15);
        } else {
            $kill = Process::fromShellCommandline('kill ' . (int) $pid);
            $kill->run();
        }

        usleep(300000);

        return !$this->isProcessRunning($pid);
    }

    private function countRunningProcessesForUser(int $userId): int
    {
        // This architecture allows only one listener per user metadata file.
        // Trust PID metadata first to avoid false positives from shell pattern matching.
        $status = $this->resolveStatus($userId);
        if ($status['running']) {
            return 1;
        }

        // Fallback scan: check process table for user-scoped listener command.
        $process = Process::fromShellCommandline('ps -eo pid=,args=');
        $process->setTimeout(5);
        $process->run();

        if (!$process->isSuccessful()) {
            return 0;
        }

        $lines = preg_split('/\r?\n/', trim((string) $process->getOutput())) ?: [];
        $count = 0;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/\sartisan\s+mqtt:subscribe\b.*--user_id=' . preg_quote((string) $userId, '/') . '(\s|$)/', $line) === 1) {
                $count++;
            }
        }

        return $count;
    }

    private function startLockPath(int $userId): string
    {
        return storage_path("app/mqtt-listener/locks/user-{$userId}.lock");
    }

    private function acquireStartLock(int $userId, int $timeoutSeconds)
    {
        $path = $this->startLockPath($userId);
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return false;
        }

        $startedAt = microtime(true);
        do {
            if (flock($handle, LOCK_EX | LOCK_NB)) {
                return $handle;
            }

            usleep(100000);
        } while ((microtime(true) - $startedAt) < $timeoutSeconds);

        fclose($handle);
        return false;
    }

    private function releaseStartLock($handle): void
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function buildStartCommand(int $userId, string $mqttUsername, string $mqttPassword, string $deviceId, string $logPath): string
    {
        return sprintf(
            'nohup %s %s mqtt:subscribe --user_id=%d --username=%s --password=%s --device_id=%s >> %s 2>&1 & echo $!',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(base_path('artisan')),
            $userId,
            escapeshellarg($mqttUsername),
            escapeshellarg($mqttPassword),
            escapeshellarg($deviceId),
            escapeshellarg($logPath)
        );
    }
}
