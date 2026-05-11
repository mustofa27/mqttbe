<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'user_id',
        'external_id',
        'tier',
        'addon_code',
        'amount',
        'currency',
        'status',
        'invoice_url',
        'payment_method',
        'paid_at',
        'expired_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billingLineItems(): HasMany
    {
        return $this->hasMany(BillingLineItem::class, 'payment_id');
    }
}
