<?php

namespace App\Http\Middleware;

use App\Models\SubscriptionPlan;
use App\Services\UsageTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $limits = SubscriptionPlan::getLimits($user->subscription_tier ?? 'free');

        // Check if subscription is expired
        if (!$user->subscription_active || 
            ($user->subscription_expires_at && $user->subscription_expires_at->isPast())) {
            if ($user->subscription_tier !== 'free') {
                return response()->json([
                    'error' => 'Subscription has expired',
                    'message' => 'Your subscription has expired. Please renew to continue using premium features.',
                ], 403);
            }
        }

        // Store plan info in request for use in controllers
        $request->merge(['plan_limits' => $limits]);

        return $next($request);
    }
}
