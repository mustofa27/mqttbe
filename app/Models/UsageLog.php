<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'message_count',
        'period_start',
        'period_end',
        'period_type',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
