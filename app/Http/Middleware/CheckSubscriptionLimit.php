<?php

namespace App\Http\Middleware;

use App\Services\PlanEnforcementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimit
{
    public function __construct(protected PlanEnforcementService $enforcementService)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if subscription is active
        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'error' => 'Subscription expired or inactive',
                'message' => 'Please upgrade or renew your subscription to continue using this feature.'
            ], 403);
        }

        // Check specific action limits
        switch ($action) {
            case 'create_project':
                if (!$user->canCreateProject()) {
                    $limits = $user->getSubscriptionLimits();
                    if (!$this->enforcementService->shouldBlock('create_project_limit', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Project limit reached',
                        'message' => "Your {$user->subscription_tier} plan allows up to {$limits['max_projects']} projects. Please upgrade to add more.",
                        'current_tier' => $user->subscription_tier,
                        'limit' => $limits['max_projects'],
                        'current_count' => $user->projects()->count()
                    ], 403);
                }
                break;

            case 'create_device':
                $projectId = $request->route('project') ?? $request->input('project_id');
                $project = $user->projects()->find($projectId);
                
                if (!$project) {
                    return response()->json(['error' => 'Project not found'], 404);
                }

                if (!$user->canAddDevice($project)) {
                    $limits = $user->getSubscriptionLimits();
                    if (!$this->enforcementService->shouldBlock('create_device_limit', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                        'project_id' => $project->id,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Device limit reached',
                        'message' => "Your {$user->subscription_tier} plan allows up to {$limits['max_devices_per_project']} devices per project. Please upgrade to add more.",
                        'current_tier' => $user->subscription_tier,
                        'limit' => $limits['max_devices_per_project'],
                        'current_count' => $project->devices()->count()
                    ], 403);
                }
                break;

            case 'create_topic':
                $projectId = $request->route('project') ?? $request->input('project_id');
                $project = $user->projects()->find($projectId);
                
                if (!$project) {
                    return response()->json(['error' => 'Project not found'], 404);
                }

                if (!$user->canAddTopic($project)) {
                    $limits = $user->getSubscriptionLimits();
                    if (!$this->enforcementService->shouldBlock('create_topic_limit', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                        'project_id' => $project->id,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Topic limit reached',
                        'message' => "Your {$user->subscription_tier} plan allows up to {$limits['max_topics_per_project']} topics per project. Please upgrade to add more.",
                        'current_tier' => $user->subscription_tier,
                        'limit' => $limits['max_topics_per_project'],
                        'current_count' => $project->topics()->count()
                    ], 403);
                }
                break;

            case 'analytics':
                if (!$user->hasFeature('analytics_enabled')) {
                    if (!$this->enforcementService->shouldBlock('feature_analytics', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Feature not available',
                        'message' => "Analytics is not available in your {$user->subscription_tier} plan. Please upgrade to access this feature.",
                        'current_tier' => $user->subscription_tier
                    ], 403);
                }
                break;

            case 'webhooks':
                if (!$user->hasFeature('webhooks_enabled')) {
                    if (!$this->enforcementService->shouldBlock('feature_webhooks', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Feature not available',
                        'message' => "Webhooks are not available in your {$user->subscription_tier} plan. Please upgrade to access this feature.",
                        'current_tier' => $user->subscription_tier
                    ], 403);
                }
                break;

            case 'api':
                if (!$user->hasFeature('api_access')) {
                    if (!$this->enforcementService->shouldBlock('feature_api_access', [
                        'user_id' => $user->id,
                        'tier' => $user->subscription_tier,
                    ])) {
                        break;
                    }

                    return response()->json([
                        'error' => 'Feature not available',
                        'message' => "API access is not available in your {$user->subscription_tier} plan. Please upgrade to access this feature.",
                        'current_tier' => $user->subscription_tier
                    ], 403);
                }
                break;
        }

        return $next($request);
    }
}
