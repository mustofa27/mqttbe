<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddon extends Model
{
    protected $fillable = [
        'user_id',
        'addon_code',
        'quantity',
        'starts_at',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(SubscriptionAddon::class, 'addon_code', 'code');
    }
}