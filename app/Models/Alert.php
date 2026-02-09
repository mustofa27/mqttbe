<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'type',
        'threshold',
        'condition',
        'recipients',
        'active',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'recipients' => 'array',
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all available alert types
     */
    public static function getAlertTypes(): array
    {
        return [
            'rate_limit_warning' => 'Rate Limit Warning (Approaching)',
            'rate_limit_exceeded' => 'Rate Limit Exceeded',
            'quota_warning' => 'Quota Warning (80% Used)',
            'high_message_volume' => 'High Message Volume',
            'subscription_expiring' => 'Subscription Expiring Soon',
        ];
    }

    /**
     * Get conditions for different alert types
     */
    public static function getConditions(string $type): array
    {
        return [
            'rate_limit_warning' => ['exceeds_80_percent', 'exceeds_90_percent'],
            'quota_warning' => ['exceeds_80_percent', 'exceeds_90_percent'],
            'high_message_volume' => ['exceeds', 'drops_below'],
            'subscription_expiring' => ['days_remaining'],
        ];
    }
}
