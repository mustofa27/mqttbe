<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\UsageLog;
use App\Services\UsageTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsageController extends Controller
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $limits = \App\Models\SubscriptionPlan::getLimits($user->subscription_tier ?? 'free');

        $projects = Project::where('user_id', $user->id)->get();
        
        $currentHourUsage = $this->usageService->getTotalHourlyUsage($user->id);
        
        $usageData = [];
        foreach ($projects as $project) {
            $summary = $this->usageService->getUsageSummary($project->id);
            $usageData[$project->id] = [
                'project' => $project,
                'current_hour' => $this->usageService->getCurrentHourUsage($project->id),
                'current_day' => $this->usageService->getCurrentDayUsage($project->id),
                'total_usage' => $summary['total_usage'],
            ];
        }

        return view('dashboard.usage', [
            'user' => $user,
            'limits' => $limits,
            'currentHourUsage' => $currentHourUsage,
            'usageData' => $usageData,
            'rateLimit' => $limits['rate_limit_per_hour'],
        ]);
    }

    public function projectUsage(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            abort(403);
        }

        $limits = \App\Models\SubscriptionPlan::getLimits($user->subscription_tier ?? 'free');
        $from = $request->query('from') ? \Carbon\Carbon::parse($request->query('from')) : \Carbon\Carbon::now()->subDays(30);
        $to = $request->query('to') ? \Carbon\Carbon::parse($request->query('to')) : \Carbon\Carbon::now();

        $summary = $this->usageService->getUsageSummary($project->id, $from, $to);
        $currentHourUsage = $this->usageService->getCurrentHourUsage($project->id);

        return view('dashboard.project-usage', [
            'project' => $project,
            'limits' => $limits,
            'summary' => $summary,
            'currentHourUsage' => $currentHourUsage,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
