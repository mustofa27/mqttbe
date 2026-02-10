<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Device;

class Topic extends Model
{
    protected $fillable = [
        'project_id',
        'code',
        'template',
        'enabled',
    ];

    /**
     * Get the project that owns this topic.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all permissions for this topic.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'topic_code', 'code');
    }

    protected static function booted(): void
    {
        static::created(function (Topic $topic) {
            Device::updateOrCreate(
                [
                    'project_id' => $topic->project_id,
                    'device_id' => 'sys_device',
                ],
                [
                    'type' => 'dashboard',
                    'active' => true,
                ]
            );
        });
    }
}
