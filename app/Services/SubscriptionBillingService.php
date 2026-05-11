<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionBillingService
{
    /**
     * Apply paid subscription payment effects: subscription updates, add-on activation, and billing entries.
     */
    public function applySuccessfulPayment(SubscriptionPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();

            $user = $payment->user;
            if (!$user) {
                return;
            }

            $metadata = is_array($payment->metadata) ? $payment->metadata : [];
            $months = $this->resolveMonths($metadata);

            if (($metadata['is_base_plan'] ?? false) === true) {
                $this->activateBasePlan($payment, $months);
            }

            $this->activateAddons($payment, $metadata, $months);
            $this->persistBillingLineItems($payment, $metadata);
        });
    }

    private function resolveMonths(array $metadata): int
    {
        $months = (int) ($metadata['months'] ?? 1);
        return max($months, 1);
    }

    private function activateBasePlan(SubscriptionPayment $payment, int $months): void
    {
        $user = $payment->user;
        if (!$user) {
            return;
        }

        $baseDate = now();
        if ($user->subscription_expires_at && $user->subscription_expires_at->isFuture()) {
            $baseDate = $user->subscription_expires_at->copy();
        }

        $user->update([
            'subscription_tier' => $payment->tier,
            'subscription_active' => true,
            'subscription_expires_at' => $baseDate->addMonths($months),
        ]);
    }

    private function activateAddons(SubscriptionPayment $payment, array $metadata, int $months): void
    {
        $user = $payment->user;
        if (!$user) {
            return;
        }

        $addonItems = $metadata['addon_items'] ?? [];
        if (!is_array($addonItems)) {
            return;
        }

        foreach ($addonItems as $item) {
            if (!is_array($item) || empty($item['code'])) {
                continue;
            }

            $code = (string) $item['code'];
            $quantity = max((int) ($item['quantity'] ?? 1), 1);
            $isRecurring = (bool) ($item['is_recurring'] ?? true);

            $startsAt = now();
            $expiresAt = $isRecurring ? now()->addMonths($months) : null;

            $existing = DB::table('user_addons')
                ->where('user_id', $user->id)
                ->where('addon_code', $code)
                ->where('active', true)
                ->orderByDesc('id')
                ->first();

            if ($existing) {
                $newQuantity = ((int) $existing->quantity) + $quantity;
                $currentExpiry = $existing->expires_at ? Carbon::parse($existing->expires_at) : null;

                if ($expiresAt && $currentExpiry && $currentExpiry->isFuture()) {
                    $expiresAt = $currentExpiry->copy()->addMonths($months);
                }

                DB::table('user_addons')
                    ->where('id', $existing->id)
                    ->update([
                        'quantity' => $newQuantity,
                        'starts_at' => $existing->starts_at ?? $startsAt,
                        'expires_at' => $expiresAt,
                        'active' => true,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('user_addons')->insert([
                'user_id' => $user->id,
                'addon_code' => $code,
                'quantity' => $quantity,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function persistBillingLineItems(SubscriptionPayment $payment, array $metadata): void
    {
        $lineItems = $metadata['line_items'] ?? [];
        if (!is_array($lineItems) || empty($lineItems)) {
            return;
        }

        DB::table('billing_line_items')
            ->where('payment_id', $payment->id)
            ->delete();

        foreach ($lineItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            DB::table('billing_line_items')->insert([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'type' => (string) ($item['type'] ?? 'base'),
                'description' => (string) ($item['description'] ?? 'Subscription charge'),
                'amount' => (float) ($item['amount'] ?? 0),
                'currency' => (string) ($item['currency'] ?? $payment->currency ?? 'IDR'),
                'period_start' => isset($item['period_start']) ? Carbon::parse($item['period_start']) : null,
                'period_end' => isset($item['period_end']) ? Carbon::parse($item['period_end']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
