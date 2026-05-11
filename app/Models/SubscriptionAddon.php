<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionAddon extends Model
{
    public const UNIT_TYPE_WEBHOOK = 'webhook';
    public const UNIT_TYPE_API_KEY = 'api_key';
    public const UNIT_TYPE_API_RPM = 'api_rpm';
    public const UNIT_TYPE_DASHBOARD_WIDGET = 'dashboard_widget';
    public const UNIT_TYPE_RETENTION_DAYS = 'retention_days';
    public const UNIT_TYPE_MONTHLY_MESSAGES = 'monthly_messages';

    protected $fillable = [
        'code',
        'name',
        'unit_type',
        'price',
        'included_units',
        'is_recurring',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'included_units' => 'integer',
        'is_recurring' => 'boolean',
        'active' => 'boolean',
    ];

    public function userAddons(): HasMany
    {
        return $this->hasMany(UserAddon::class, 'addon_code', 'code');
    }

    public static function unitTypeOptions(): array
    {
        return [
            self::UNIT_TYPE_WEBHOOK => 'Webhook Endpoints',
            self::UNIT_TYPE_API_KEY => 'API Keys',
            self::UNIT_TYPE_API_RPM => 'API RPM Boost',
            self::UNIT_TYPE_DASHBOARD_WIDGET => 'Dashboard Widgets',
            self::UNIT_TYPE_RETENTION_DAYS => 'Retention Days',
            self::UNIT_TYPE_MONTHLY_MESSAGES => 'Monthly Messages',
        ];
    }

    public static function unitTypeKeys(): array
    {
        return array_keys(self::unitTypeOptions());
    }

    public static function labelFor(?string $unitType): string
    {
        if (!$unitType) {
            return '-';
        }

        return self::unitTypeOptions()[$unitType] ?? $unitType;
    }

    public function getUnitTypeLabelAttribute(): string
    {
        return self::labelFor($this->unit_type);
    }
}
