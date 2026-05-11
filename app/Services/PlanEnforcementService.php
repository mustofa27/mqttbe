<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PlanEnforcementService
{
    public function isHardEnforcementEnabled(): bool
    {
        return (bool) config('pricing.plan_hard_enforce', false);
    }

    /**
     * Log a plan violation and decide whether request should be blocked.
     */
    public function shouldBlock(string $violation, array $context = []): bool
    {
        Log::warning('Plan enforcement violation', array_merge([
            'violation' => $violation,
            'hard_enforce' => $this->isHardEnforcementEnabled(),
        ], $context));

        return $this->isHardEnforcementEnabled();
    }
}
