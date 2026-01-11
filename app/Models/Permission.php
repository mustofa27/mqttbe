<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = [
        'project_id',
        'device_type',
        'topic_code',
        'access',
    ];

    /**
     * Get the project that owns the permission.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the topic for this permission.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_code', 'code');
    }
}
