<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'project_id',
        'device_id',
        'type',
        'active',
    ];

    /**
     * Get the project that owns the device.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
