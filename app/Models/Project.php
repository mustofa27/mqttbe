<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'project_key',
        'project_secret',
        'project_secret_plain',
        'active',
    ];

    protected $hidden = [
        'project_secret',
        'project_secret_plain',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all devices for this project.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get all topics for this project.
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }
    /**
     * Regenerate project secret and update fields.
     * @param string $newSecret
     */
    public function regenerateSecret(string $newSecret): void
    {
        $this->project_secret = \Illuminate\Support\Facades\Hash::make($newSecret);
        $this->project_secret_plain = \Illuminate\Support\Facades\Crypt::encryptString($newSecret);
        $this->save();
    }
}
