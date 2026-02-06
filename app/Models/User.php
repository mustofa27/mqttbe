<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_tier',
        'subscription_expires_at',
        'subscription_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'subscription_active' => 'boolean',
        ];
    }

    /**
     * Get all projects owned by the user.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all devices through projects.
     */
    public function devices()
    {
        return Device::whereIn('project_id', $this->projects()->pluck('id'))->get();
    }

    /**
     * Get subscription limits for this user.
     */
    public function getSubscriptionLimits(): array
    {
        return SubscriptionPlan::getLimits($this->subscription_tier ?? 'free');
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->subscription_active) {
            return false;
        }

        // Free tier never expires
        if ($this->subscription_tier === 'free') {
            return true;
        }

        // Check if paid subscription has expired
        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can create more projects.
     */
    public function canCreateProject(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $limits = $this->getSubscriptionLimits();
        $maxProjects = $limits['max_projects'];

        if (SubscriptionPlan::isUnlimited($maxProjects)) {
            return true;
        }

        return $this->projects()->count() < $maxProjects;
    }

    /**
     * Check if user can add more devices to a project.
     */
    public function canAddDevice(Project $project): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $limits = $this->getSubscriptionLimits();
        $maxDevices = $limits['max_devices_per_project'];

        if (SubscriptionPlan::isUnlimited($maxDevices)) {
            return true;
        }

        return $project->devices()->count() < $maxDevices;
    }

    /**
     * Check if user can add more topics to a project.
     */
    public function canAddTopic(Project $project): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $limits = $this->getSubscriptionLimits();
        $maxTopics = $limits['max_topics_per_project'];

        if (SubscriptionPlan::isUnlimited($maxTopics)) {
            return true;
        }

        return $project->topics()->count() < $maxTopics;
    }

    /**
     * Check if user has access to a feature.
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $limits = $this->getSubscriptionLimits();
        return $limits[$feature] ?? false;
    }
}
