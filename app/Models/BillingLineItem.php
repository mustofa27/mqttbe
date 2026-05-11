<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingLineItem extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'type',
        'description',
        'amount',
        'currency',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'payment_id');
    }
}