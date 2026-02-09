<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    protected $fillable = [
        'project_id',
        'url',
        'event_type',
        'description',
        'headers',
        'active',
        'last_triggered_at',
        'failure_count',
        'last_failure_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all available webhook event types
     */
    public static function getEventTypes(): array
    {
        return [
            'message_published' => 'Message Published',
            'rate_limit_exceeded' => 'Rate Limit Exceeded',
            'quota_warning' => 'Quota Warning (80%)',
            'subscription_expiring' => 'Subscription Expiring Soon',
        ];
    }
}
