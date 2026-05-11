<?php

namespace App\Http\Middleware;

use App\Services\EntitlementService;
use App\Services\PlanEnforcementService;
use App\Services\UsageTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    protected EntitlementService $entitlementService;
    protected PlanEnforcementService $enforcementService;
    protected UsageTrackingService $usageService;

    public function __construct(
        EntitlementService $entitlementService,
        PlanEnforcementService $enforcementService,
        UsageTrackingService $usageService
    )
    {
        $this->entitlementService = $entitlementService;
        $this->enforcementService = $enforcementService;
        $this->usageService = $usageService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $limits = $this->entitlementService->getEffectiveLimits($user);

        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'error' => 'Subscription has expired',
                'message' => 'Your subscription has expired. Please renew to continue using premium features.',
            ], 403);
        }

        if (!$this->entitlementService->hasFeature($user, 'api_access')) {
            if (!$this->enforcementService->shouldBlock('feature_api_access', [
                'user_id' => $user->id,
                'tier' => $user->subscription_tier,
                'path' => $request->path(),
            ])) {
                $request->merge(['plan_limits' => $limits]);
                return $next($request);
            }

            return response()->json([
                'error' => 'Feature not available',
                'message' => "API access is not available in your {$user->subscription_tier} plan. Please upgrade to access this feature.",
                'current_tier' => $user->subscription_tier,
            ], 403);
        }

        // Store plan info in request for use in controllers
        $request->merge(['plan_limits' => $limits]);

        return $next($request);
    }
}
