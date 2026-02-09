<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWebhookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Webhook $webhook,
        public array $payload
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $webhookService->send($this->webhook, $this->payload);
    }
}
