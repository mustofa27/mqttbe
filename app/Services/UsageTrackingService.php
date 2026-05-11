<?php

namespace App\Services;

use App\Models\Project;
use App\Models\UsageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UsageTrackingService
{
    /**
     * Record a message usage for a project
     */
    public function recordMessage(int $projectId, int $count = 1): void
    {
        $project = Project::findOrFail($projectId);
        $user = $project->user;
        
        $now = Carbon::now();
        $hourStart = $now->clone()->startOfHour();
        $hourEnd = $now->clone()->endOfHour();

        UsageLog::updateOrCreate(
            [
                'project_id' => $projectId,
                'user_id' => $user->id,
                'period_type' => 'hour',
                'period_start' => $hourStart,
            ],
            [
                'period_end' => $hourEnd,
                'message_count' => DB::raw("message_count + {$count}"),
            ]
        );
    }

    /**
     * Get current hour usage for a project
     */
    public function getCurrentHourUsage(int $projectId): int
    {
        $now = Carbon::now();
        $hourStart = $now->clone()->startOfHour();

        $log = UsageLog::where('project_id', $projectId)
            ->where('period_type', 'hour')
            ->where('period_start', $hourStart)
            ->first();

        return $log?->message_count ?? 0;
    }

    /**
     * Get current day usage for a project
     */
    public function getCurrentDayUsage(int $projectId): int
    {
        $now = Carbon::now();
        $dayStart = $now->clone()->startOfDay();

        $logs = UsageLog::where('project_id', $projectId)
            ->where('period_type', 'hour')
            ->whereDate('period_start', $dayStart)
            ->sum('message_count');

        return $logs ?? 0;
    }

    /**
     * Check if project has exceeded hourly rate limit
     */
    public function hasExceededRateLimit(int $projectId): bool
    {
        $status = $this->getLimitStatus($projectId);

        return $status['hourly_exceeded'] || $status['monthly_exceeded'];
    }

    /**
     * Get subscription limits for user
     */
    public function getUserLimits(User $user): array
    {
        return $user->getSubscriptionLimits();
    }

    /**
     * Get current month usage for a project.
     */
    public function getCurrentMonthUsage(int $projectId): int
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return (int) UsageLog::where('project_id', $projectId)
            ->where('period_type', 'hour')
            ->whereBetween('period_start', [$monthStart, $monthEnd])
            ->sum('message_count');
    }

    /**
     * Build detailed hourly and monthly limit status for a project.
     */
    public function getLimitStatus(int $projectId): array
    {
        $project = Project::findOrFail($projectId);
        $limits = $this->getUserLimits($project->user);

        $hourlyLimit = (int) ($limits['rate_limit_per_hour'] ?? 0);
        $monthlyLimit = (int) ($limits['max_monthly_messages'] ?? 0);

        $currentHourlyUsage = $this->getCurrentHourUsage($projectId);
        $currentMonthlyUsage = $this->getCurrentMonthUsage($projectId);

        $hourlyExceeded = $hourlyLimit !== -1 && $hourlyLimit > 0 && $currentHourlyUsage >= $hourlyLimit;
        $monthlyExceeded = $monthlyLimit !== -1 && $monthlyLimit > 0 && $currentMonthlyUsage >= $monthlyLimit;

        return [
            'hourly_exceeded' => $hourlyExceeded,
            'monthly_exceeded' => $monthlyExceeded,
            'hourly_limit' => $hourlyLimit,
            'monthly_limit' => $monthlyLimit,
            'current_hourly_usage' => $currentHourlyUsage,
            'current_monthly_usage' => $currentMonthlyUsage,
        ];
    }

    /**
     * Get today's usage for a project
     */
    public function getTodayUsage(int $projectId): int
    {
        $today = Carbon::now()->startOfDay();
        $tomorrow = $today->clone()->addDay();

        return UsageLog::where('project_id', $projectId)
            ->whereBetween('period_start', [$today, $tomorrow])
            ->sum('message_count');
    }

    /**
     * Get user's total hourly usage across all projects
     */
    public function getTotalHourlyUsage(int $userId): int
    {
        $now = Carbon::now();
        $hourStart = $now->clone()->startOfHour();

        return UsageLog::where('user_id', $userId)
            ->where('period_type', 'hour')
            ->where('period_start', $hourStart)
            ->sum('message_count');
    }

    /**
     * Get usage summary for a project
     */
    public function getUsageSummary(int $projectId, Carbon $from = null, Carbon $to = null): array
    {
        $from = $from ?? Carbon::now()->subDays(30);
        $to = $to ?? Carbon::now();

        $logs = UsageLog::where('project_id', $projectId)
            ->whereBetween('period_start', [$from, $to])
            ->get()
            ->groupBy('period_type');

        return [
            'hourly_usage' => $logs->get('hour', collect())->sum('message_count'),
            'total_usage' => $logs->flatten()->sum('message_count'),
            'daily_breakdown' => $logs->get('hour', collect())
                ->groupBy(fn ($log) => $log->period_start->format('Y-m-d'))
                ->map(fn ($group) => $group->sum('message_count')),
        ];
    }
}
