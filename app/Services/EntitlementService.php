<?php

namespace App\Services;

use App\Models\AdvanceDashboardWidget;
use App\Models\ApiKey;
use App\Models\SubscriptionPlan;
use App\Models\UserAddon;
use App\Models\User;
use App\Models\Webhook;

class EntitlementService
{
    /**
     * Resolve effective limits from base plan plus active add-ons.
     */
    public function getEffectiveLimits(User $user): array
    {
        $plan = SubscriptionPlan::getLimits($user->subscription_tier ?? 'free');
        if (!$plan) {
            return [];
        }

        $limits = $plan->toArray();

        $addons = UserAddon::with('addon')
            ->where('user_id', $user->id)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->whereHas('addon', fn ($query) => $query->where('active', true))
            ->get();

        foreach ($addons as $addon) {
            if (!$addon->addon) {
                continue;
            }

            $units = (int) $addon->addon->included_units * max((int) $addon->quantity, 1);
            $this->applyAddon($limits, (string) $addon->addon->unit_type, (string) $addon->addon->code, $units);
        }

        return $limits;
    }

    /**
     * Check if user has access to a feature under effective limits.
     */
    public function hasFeature(User $user, string $feature): bool
    {
        if (!$user->hasActiveSubscription()) {
            return false;
        }

        $limits = $this->getEffectiveLimits($user);
        return (bool) ($limits[$feature] ?? false);
    }

    /**
     * Check whether user has reached a hard cap for a specific metric.
     */
    public function isHardBlocked(User $user, string $metric): bool
    {
        if (!$user->hasActiveSubscription()) {
            return true;
        }

        $limits = $this->getEffectiveLimits($user);

        return match ($metric) {
            'projects' => $this->isCountBlocked((int) ($limits['max_projects'] ?? 0), $user->projects()->count()),
            'api_keys' => $this->isCountBlocked((int) ($limits['max_api_keys'] ?? 0), ApiKey::where('user_id', $user->id)->where('is_active', true)->count()),
            'webhooks' => $this->isCountBlocked(
                (int) ($limits['max_webhooks_per_project'] ?? 0),
                Webhook::whereHas('project', fn ($q) => $q->where('user_id', $user->id))->where('active', true)->count()
            ),
            'advance_dashboard_widgets' => $this->isCountBlocked(
                (int) ($limits['max_advance_dashboard_widgets'] ?? 0),
                AdvanceDashboardWidget::where('user_id', $user->id)->count()
            ),
            default => false,
        };
    }

    private function applyAddon(array &$limits, string $unitType, string $code, int $units): void
    {
        if ($units <= 0) {
            return;
        }

        $normalized = strtolower($unitType . ' ' . $code);

        if (str_contains($normalized, 'webhook')) {
            $limits['webhooks_enabled'] = true;
            $this->addLimit($limits, 'max_webhooks_per_project', $units);
        }

        if (str_contains($normalized, 'api key')) {
            $limits['api_access'] = true;
            $this->addLimit($limits, 'max_api_keys', $units);
        }

        if (str_contains($normalized, 'api_rpm') || str_contains($normalized, 'rpm')) {
            $limits['api_access'] = true;
            $this->addLimit($limits, 'api_rpm', $units);
        }

        if (str_contains($normalized, 'dashboard') || str_contains($normalized, 'widget')) {
            $limits['advanced_analytics_enabled'] = true;
            $this->addLimit($limits, 'max_advance_dashboard_widgets', $units);
        }

        if (str_contains($normalized, 'retention')) {
            $this->addLimit($limits, 'data_retention_days', $units);
        }

        if (str_contains($normalized, 'message')) {
            $this->addLimit($limits, 'max_monthly_messages', $units);
        }
    }

    private function addLimit(array &$limits, string $key, int $delta): void
    {
        $current = (int) ($limits[$key] ?? 0);

        // -1 means unlimited cap and should not be modified.
        if ($current === -1) {
            return;
        }

        $limits[$key] = $current + $delta;
    }

    private function isCountBlocked(int $limit, int $currentCount): bool
    {
        if ($limit === -1) {
            return false;
        }

        return $currentCount >= $limit;
    }
}
