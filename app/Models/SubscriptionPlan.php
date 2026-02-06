<?php

namespace App\Models;

class SubscriptionPlan
{
    public static function getLimits(string $tier): array
    {
        $plans = [
            'free' => [
                'max_projects' => 1,
                'max_devices_per_project' => 5,
                'max_topics_per_project' => 3,
                'rate_limit_per_hour' => 100,
                'data_retention_days' => 30,
                'analytics_enabled' => false,
                'webhooks_enabled' => false,
                'api_access' => false,
                'priority_support' => false,
            ],
            'starter' => [
                'max_projects' => 5,
                'max_devices_per_project' => 50,
                'max_topics_per_project' => 20,
                'rate_limit_per_hour' => 1000,
                'data_retention_days' => 90,
                'analytics_enabled' => true,
                'webhooks_enabled' => false,
                'api_access' => true,
                'priority_support' => false,
            ],
            'professional' => [
                'max_projects' => 20,
                'max_devices_per_project' => 500,
                'max_topics_per_project' => 100,
                'rate_limit_per_hour' => 10000,
                'data_retention_days' => 365,
                'analytics_enabled' => true,
                'webhooks_enabled' => true,
                'api_access' => true,
                'priority_support' => true,
            ],
            'enterprise' => [
                'max_projects' => -1, // unlimited
                'max_devices_per_project' => -1, // unlimited
                'max_topics_per_project' => -1, // unlimited
                'rate_limit_per_hour' => -1, // unlimited
                'data_retention_days' => -1, // unlimited
                'analytics_enabled' => true,
                'webhooks_enabled' => true,
                'api_access' => true,
                'priority_support' => true,
            ],
        ];

        return $plans[$tier] ?? $plans['free'];
    }

    public static function getTiers(): array
    {
        return ['free', 'starter', 'professional', 'enterprise'];
    }

    public static function isUnlimited(int $limit): bool
    {
        return $limit === -1;
    }
}
