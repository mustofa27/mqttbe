<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Project;
use App\Mail\AlertNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AlertService
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Check and trigger alerts for a project
     */
    public function checkAlerts(Project $project): void
    {
        $alerts = Alert::where('project_id', $project->id)
            ->where('active', true)
            ->get();

        foreach ($alerts as $alert) {
            if ($this->shouldTrigger($alert, $project)) {
                $this->trigger($alert, $project);
            }
        }
    }

    /**
     * Determine if an alert should be triggered
     */
    private function shouldTrigger(Alert $alert, Project $project): bool
    {
        // Prevent duplicate triggers within 1 hour
        if ($alert->last_triggered_at && $alert->last_triggered_at->diffInMinutes(now()) < 60) {
            return false;
        }

        switch ($alert->type) {
            case 'rate_limit_warning':
                return $this->checkRateLimitWarning($project, $alert);
            case 'rate_limit_exceeded':
                return $this->checkRateLimitExceeded($project, $alert);
            case 'quota_warning':
                return $this->checkQuotaWarning($project, $alert);
            case 'subscription_expiring':
                return $this->checkSubscriptionExpiring($project, $alert);
            default:
                return false;
        }
    }

    /**
     * Check rate limit warning (approaching 80% or 90%)
     */
    private function checkRateLimitWarning(Project $project, Alert $alert): bool
    {
        $currentUsage = $this->usageService->getCurrentHourUsage($project->id);
        $limit = $project->user->getCurrentSubscriptionPlan()->rate_limit_per_hour;

        $percentageUsed = ($currentUsage / $limit) * 100;

        if ($alert->condition === 'exceeds_80_percent') {
            return $percentageUsed >= 80;
        } elseif ($alert->condition === 'exceeds_90_percent') {
            return $percentageUsed >= 90;
        }

        return false;
    }

    /**
     * Check if rate limit exceeded
     */
    private function checkRateLimitExceeded(Project $project, Alert $alert): bool
    {
        return $this->usageService->hasExceededRateLimit($project->id);
    }

    /**
     * Check quota warning (80% of monthly limit)
     */
    private function checkQuotaWarning(Project $project, Alert $alert): bool
    {
        $dailyUsage = $this->usageService->getTodayUsage($project->id);
        $monthlyLimit = $project->user->getCurrentSubscriptionPlan()->data_retention_days * 1000; // Simplified

        $percentageUsed = ($dailyUsage / $monthlyLimit) * 100;

        if ($alert->condition === 'exceeds_80_percent') {
            return $percentageUsed >= 80;
        } elseif ($alert->condition === 'exceeds_90_percent') {
            return $percentageUsed >= 90;
        }

        return false;
    }

    /**
     * Check if subscription is expiring soon
     */
    private function checkSubscriptionExpiring(Project $project, Alert $alert): bool
    {
        $user = $project->user;
        if (!$user->subscription_ends_at) {
            return false;
        }

        $daysRemaining = $user->subscription_ends_at->diffInDays(now());

        return $daysRemaining <= ($alert->threshold ?? 7);
    }

    /**
     * Trigger an alert (send email)
     */
    private function trigger(Alert $alert, Project $project): void
    {
        $message = $this->generateAlertMessage($alert, $project);

        foreach ($alert->recipients as $email) {
            Mail::to($email)->queue(new AlertNotification($alert, $message));
        }

        $alert->update([
            'last_triggered_at' => now(),
            'trigger_count' => $alert->trigger_count + 1,
        ]);

        Log::info("Alert {$alert->id} triggered for project {$project->id}");
    }

    /**
     * Generate alert message based on type
     */
    private function generateAlertMessage(Alert $alert, Project $project): string
    {
        switch ($alert->type) {
            case 'rate_limit_warning':
                $usage = $this->usageService->getCurrentHourUsage($project->id);
                $limit = $project->user->getCurrentSubscriptionPlan()->rate_limit_per_hour;
                $percentage = round(($usage / $limit) * 100, 1);
                return "Your project '{$project->name}' is at {$percentage}% of its hourly message limit ({$usage}/{$limit} messages).";

            case 'rate_limit_exceeded':
                $limit = $project->user->getCurrentSubscriptionPlan()->rate_limit_per_hour;
                return "Your project '{$project->name}' has exceeded its hourly message limit of {$limit} messages.";

            case 'quota_warning':
                return "Your project '{$project->name}' is approaching its data storage quota.";

            case 'subscription_expiring':
                $daysRemaining = $project->user->subscription_ends_at?->diffInDays(now()) ?? 0;
                return "Your subscription expires in {$daysRemaining} days.";

            default:
                return "Alert triggered for project '{$project->name}'.";
        }
    }

    /**
     * Get all active alerts for a project
     */
    public function getProjectAlerts(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return Alert::where('project_id', $project->id)->orderBy('created_at', 'desc')->get();
    }
}
