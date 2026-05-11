<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionAddon extends Model
{
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
}
