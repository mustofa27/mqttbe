<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\User;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans (admin only).
     */
    public function index()
    {
        $defaultTier = self::getDefaultPlanTier();
        $plans = [
            'free' => $this->getPlanDetails('free'),
            'starter' => $this->getPlanDetails('starter'),
            'professional' => $this->getPlanDetails('professional'),
            'enterprise' => $this->getPlanDetails('enterprise'),
        ];
        // Optionally, highlight or select the default plan in the view
        return view('admin.subscription-plans.index', compact('plans', 'defaultTier'));
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
            'price' => 'required|numeric|min:0',
            'max_projects' => 'required|integer',
            'max_devices_per_project' => 'required|integer',
            'max_topics_per_project' => 'required|integer',
            'rate_limit_per_hour' => 'required|integer',
            'max_monthly_messages' => 'required|integer',
            'data_retention_days' => 'required|integer',
            'analytics_enabled' => 'boolean',
            'advanced_analytics_enabled' => 'boolean',
            'max_advance_dashboard_widgets' => 'required|integer',
            'webhooks_enabled' => 'boolean',
            'max_webhooks_per_project' => 'required|integer',
            'api_access' => 'boolean',
            'max_api_keys' => 'required|integer',
            'api_rpm' => 'required|integer',
            'priority_support' => 'boolean',
        ]);

        $validated['analytics_enabled'] = $request->boolean('analytics_enabled');
        $validated['advanced_analytics_enabled'] = $request->boolean('advanced_analytics_enabled');
        $validated['webhooks_enabled'] = $request->boolean('webhooks_enabled');
        $validated['api_access'] = $request->boolean('api_access');
        $validated['priority_support'] = $request->boolean('priority_support');

        $planModel = SubscriptionPlan::where('tier', $plan)->firstOrFail();
        $planModel->update($validated);

        return redirect()->route('admin.subscription-plans.index')
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

        // Reset the plan to default values from DB (assume original values are stored in DB)
        $defaultPlan = SubscriptionPlan::where('tier', $plan)->first();
        if ($defaultPlan) {
            $defaultValues = $defaultPlan->getOriginal();
            // Remove keys that shouldn't be updated
            unset($defaultValues['id'], $defaultValues['tier'], $defaultValues['created_at'], $defaultValues['updated_at']);
            $defaultPlan->update($defaultValues);
        }
        return back()->with('success', ucfirst($plan) . ' subscription plan reset to default.');
    }
    /**
     * Get the default subscription plan tier.
     */
    public static function getDefaultPlanTier()
    {
        return 'free';
    }
    /**
     * Get plan details including cached overrides.
     */
    private function getPlanDetails($plan)
    {
        $model = SubscriptionPlan::getLimits($plan);
        return $model ? $model->toArray() : [];
    }

    /**
     * Get plans with user counts.
     */
    public function statistics()
    {
        $stats = [
            'free' => User::where('subscription_tier', 'free')->count(),
            'starter' => User::where('subscription_tier', 'starter')->count(),
            'professional' => User::where('subscription_tier', 'professional')->count(),
            'enterprise' => User::where('subscription_tier', 'enterprise')->count(),
        ];

        return view('admin.subscription-plans.statistics', compact('stats'));
    }
}
