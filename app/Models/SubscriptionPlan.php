<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'tier',
        'price',
        'max_projects',
        'max_devices_per_project',
        'max_topics_per_project',
        'rate_limit_per_hour',
        'data_retention_days',
        'analytics_enabled',
        'advanced_analytics_enabled',
        'webhooks_enabled',
        'api_access',
        'priority_support',
    ];

    // Optionally, add helper methods to fetch limits by tier
    public static function getLimits(string $tier): ?self
    {
        return self::where('tier', $tier)->first();
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
