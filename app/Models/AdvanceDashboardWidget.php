<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceDashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'topic_id',
        'title',
        'data_type',
        'visualization_mode',
        'json_key',
        'json_key_type',
        'size',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'size' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
