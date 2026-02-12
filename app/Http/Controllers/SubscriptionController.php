<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Topic;
use App\Models\SubscriptionPayment;
use App\Services\PaypoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Display the user's subscription information.
     */
    public function index()
    {
        $user = auth()->user();
        $currentLimits = $user->getSubscriptionLimits();
        
        // Get usage stats
        $usage = [
            'projects' => [
                'current' => $user->projects()->count(),
                'limit' => $currentLimits['max_projects'],
                'unlimited' => SubscriptionPlan::isUnlimited($currentLimits['max_projects']),
            ],
        ];

        // Calculate device and topic usage across all projects
        $totalDevices = 0;
        $totalTopics = 0;
        foreach ($user->projects as $project) {
            $totalDevices += $project->devices()->count();
            $totalTopics += $project->topics()->count();
        }

        $usage['devices'] = [
            'current' => $totalDevices,
            'limit_per_project' => $currentLimits['max_devices_per_project'],
            'unlimited' => SubscriptionPlan::isUnlimited($currentLimits['max_devices_per_project']),
        ];

        $usage['topics'] = [
            'current' => $totalTopics,
            'limit_per_project' => $currentLimits['max_topics_per_project'],
            'unlimited' => SubscriptionPlan::isUnlimited($currentLimits['max_topics_per_project']),
        ];

        // Get all available plans
        $allPlans = [];
        foreach (SubscriptionPlan::getTiers() as $tier) {
            $allPlans[$tier] = SubscriptionPlan::getLimits($tier);
        }

        // Get recent payments
        $payments = SubscriptionPayment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.subscription.index', compact('user', 'currentLimits', 'usage', 'allPlans', 'payments'));
    }

    /**
     * API endpoint to get current user's limits.
     */
    public function limits()
    {
        $user = auth()->user();
        
        return response()->json([
            'subscription_tier' => $user->subscription_tier,
            'subscription_active' => $user->hasActiveSubscription(),
            'subscription_expires_at' => $user->subscription_expires_at,
            'limits' => $user->getSubscriptionLimits(),
            'usage' => [
                'projects' => $user->projects()->count(),
                'devices_total' => $user->devices()->count(),
                'topics_total' => Topic::whereIn('project_id', $user->projects->pluck('id'))->count(),
            ],
        ]);
    }

    /**
     * Display upgrade options.
     */
    public function upgrade()
    {
        $user = auth()->user();
        $currentTier = $user->subscription_tier;
        
        $plans = [];
        foreach (SubscriptionPlan::getTiers() as $tier) {
            if ($tier !== 'free') {
                $plans[$tier] = SubscriptionPlan::getLimits($tier);
            }
        }

        return view('dashboard.subscription.upgrade', compact('user', 'currentTier', 'plans'));
    }

    /**
     * Process subscription upgrade (placeholder - integrate with payment gateway).
     */
    public function processUpgrade(Request $request, PaypoolService $paypool)
    {
        $validated = $request->validate([
            'tier' => 'required|in:starter,professional,enterprise',
            'months' => 'nullable|integer|min:1|max:36',
        ]);

        $user = auth()->user();
        $tier = $validated['tier'];
        $months = isset($validated['months']) ? (int)$validated['months'] : 1;
        $plan = SubscriptionPlan::where('tier', $tier)->firstOrFail();
        $amount = (int) $plan->price * $months;

        // Generate unique external ID
        $externalId = 'SUB-' . $user->id . '-' . strtoupper($tier) . '-' . time();

        // Create payment record
        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'external_id' => $externalId,
            'tier' => $tier,
            'amount' => $amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'tier' => $tier,
                'months' => $months,
            ],
        ]);

        // Create payment via Paypool
        $result = $paypool->createPayment([
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => 'IDR',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'description' => 'ICMQTT ' . ucfirst($tier) . ' Subscription (' . $months . ' month' . ($months > 1 ? 's' : '') . ')',
            'metadata' => [
                'user_id' => $user->id,
                'tier' => $tier,
                'months' => $months,
                'payment_id' => $payment->id,
            ],
            'success_redirect_url' => route('subscription.payment.success', ['external_id' => $externalId]),
            'failure_redirect_url' => route('subscription.payment.failed', ['external_id' => $externalId]),
        ]);

        if (!$result['success'] || !isset($result['data']['invoice_url'])) {
            \Log::error('Paypool payment creation response', ['result' => $result]);
            $payment->update(['status' => 'failed']);
            $errorMsg = $result['error'] ?? 'Failed to create payment. Please try again.';
            if (isset($result['data']) && !isset($result['data']['invoice_url'])) {
                $errorMsg = 'Payment created but invoice URL is missing. Please contact support.';
            }
            return back()->withErrors([
                'payment' => $errorMsg
            ]);
        }

        // Update payment with invoice URL
        $payment->update([
            'invoice_url' => $result['data']['invoice_url'],
            'expired_at' => $result['data']['expired_at'] ?? now()->addHours(24),
        ]);

        // Redirect to Paypool invoice
        return redirect($result['data']['invoice_url']);
    }

    /**
     * Cancel subscription.
     */
    public function cancel()
    {
        $user = auth()->user();
        
        // Downgrade to free tier
        $user->update([
            'subscription_tier' => 'free',
            'subscription_active' => true,
            'subscription_expires_at' => null,
        ]);

        return redirect()
            ->route('subscription.index')
            ->with('success', 'Subscription cancelled. You are now on the free plan.');
    }

    /**
     * Payment success callback
     */
    public function paymentSuccess(Request $request)
    {
        $externalId = $request->query('external_id');
        
        if (!$externalId) {
            return redirect()->route('subscription.index')
                ->with('error', 'Invalid payment reference.');
        }

        $payment = SubscriptionPayment::where('external_id', $externalId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$payment) {
            return redirect()->route('subscription.index')
                ->with('error', 'Payment not found.');
        }

        return view('dashboard.subscription.payment-success', compact('payment'));
    }

    /**
     * Payment failed callback
     */
    public function paymentFailed(Request $request)
    {
        $externalId = $request->query('external_id');
        
        $payment = null;
        if ($externalId) {
            $payment = SubscriptionPayment::where('external_id', $externalId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('dashboard.subscription.payment-failed', compact('payment'));
    }
}
