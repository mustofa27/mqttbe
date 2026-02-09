<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Trigger a webhook event
     */
    public function trigger(Project $project, string $eventType, array $payload): void
    {
        $webhooks = Webhook::where('project_id', $project->id)
            ->where('event_type', $eventType)
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            dispatch(new \App\Jobs\SendWebhookJob($webhook, $payload));
        }
    }

    /**
     * Test a webhook
     */
    public function test(Webhook $webhook): bool
    {
        $testPayload = [
            'event' => $webhook->event_type,
            'test' => true,
            'timestamp' => now()->toIso8601String(),
            'project_id' => $webhook->project_id,
            'message' => 'This is a test webhook event',
        ];

        return $this->send($webhook, $testPayload);
    }

    /**
     * Send webhook to URL
     */
    public function send(Webhook $webhook, array $payload): bool
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'ICMQTT-Webhook/1.0',
                    'X-Webhook-ID' => $webhook->id,
                    'X-Webhook-Event' => $webhook->event_type,
                    'X-Webhook-Timestamp' => now()->toIso8601String(),
                    ...(is_array($webhook->headers) ? $webhook->headers : []),
                ])
                ->post($webhook->url, $payload);

            if ($response->successful()) {
                $webhook->update([
                    'last_triggered_at' => now(),
                    'failure_count' => 0,
                ]);
                Log::info("Webhook {$webhook->id} sent successfully to {$webhook->url}");
                return true;
            }

            $this->recordFailure($webhook);
            Log::error("Webhook {$webhook->id} failed with status {$response->status()}");
            return false;

        } catch (\Exception $e) {
            $this->recordFailure($webhook);
            Log::error("Webhook {$webhook->id} error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Record webhook failure
     */
    private function recordFailure(Webhook $webhook): void
    {
        $failureCount = $webhook->failure_count + 1;

        $webhook->update([
            'failure_count' => $failureCount,
            'last_failure_at' => now(),
            'active' => $failureCount < 10, // Disable after 10 failures
        ]);
    }

    /**
     * Get webhook statistics
     */
    public function getStats(Webhook $webhook): array
    {
        return [
            'total_triggers' => 0, // Would need a webhook_logs table for this
            'last_triggered' => $webhook->last_triggered_at?->diffForHumans(),
            'failure_count' => $webhook->failure_count,
            'is_active' => $webhook->active,
        ];
    }
}
