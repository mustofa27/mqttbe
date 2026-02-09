<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans (admin only).
     */
    public function index()
    {
        $plans = [
            'free' => $this->getPlanDetails('free'),
            'starter' => $this->getPlanDetails('starter'),
            'professional' => $this->getPlanDetails('professional'),
            'enterprise' => $this->getPlanDetails('enterprise'),
        ];

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for editing a subscription plan (admin only).
     */
    public function edit($plan)
    {
        $validPlans = ['free', 'starter', 'professional', 'enterprise'];
        if (!in_array($plan, $validPlans)) {
            abort(404);
        }

        $planDetails = $this->getPlanDetails($plan);
        return view('admin.subscription-plans.edit', compact('plan', 'planDetails'));
    }

    /**
     * Update the specified subscription plan in storage (admin only).
     */
    public function update(Request $request, $plan)
    {
        $validPlans = ['free', 'starter', 'professional', 'enterprise'];
        if (!in_array($plan, $validPlans)) {
            abort(404);
        }

        $validated = $request->validate([
            'max_projects' => 'required|integer',
            'max_devices_per_project' => 'required|integer',
            'max_topics_per_project' => 'required|integer',
            'rate_limit_per_hour' => 'required|integer',
            'data_retention_days' => 'required|integer',
            'analytics_enabled' => 'boolean',
            'webhooks_enabled' => 'boolean',
            'api_access' => 'boolean',
            'priority_support' => 'boolean',
        ]);

        // Store plan configuration in a cache or database
        // For now, we'll use Laravel cache to store temporary overrides
        cache()->put("subscription_plan_{$plan}", $validated, now()->addDays(365));

        return redirect()->route('subscription-plans.index')
            ->with('success', ucfirst($plan) . ' subscription plan updated successfully.');
    }

    /**
     * Reset a subscription plan to default values.
     */
    public function reset($plan)
    {
        $validPlans = ['free', 'starter', 'professional', 'enterprise'];
        if (!in_array($plan, $validPlans)) {
            abort(404);
        }

        cache()->forget("subscription_plan_{$plan}");

        return back()->with('success', ucfirst($plan) . ' subscription plan reset to default.');
    }

    /**
     * Get plan details including cached overrides.
     */
    private function getPlanDetails($plan)
    {
        // Check if there are cached overrides
        if (cache()->has("subscription_plan_{$plan}")) {
            return cache()->get("subscription_plan_{$plan}");
        }

        // Otherwise get from SubscriptionPlan model
        return \App\Models\SubscriptionPlan::getLimits($plan);
    }

    /**
     * Get plans with user counts.
     */
    public function statistics()
    {
        $stats = [
            'free' => \App\Models\User::where('subscription_tier', 'free')->count(),
            'starter' => \App\Models\User::where('subscription_tier', 'starter')->count(),
            'professional' => \App\Models\User::where('subscription_tier', 'professional')->count(),
            'enterprise' => \App\Models\User::where('subscription_tier', 'enterprise')->count(),
        ];

        return view('admin.subscription-plans.statistics', compact('stats'));
    }
}
