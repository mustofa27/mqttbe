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

        // Generate unique external ID with random string
        $externalId = 'SUB-' . $user->id . '-' . strtoupper($tier) . '-' . time() . '-' . Str::random(6);

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
            'webhook_url' => config('paypool.webhook_url'),
        ]);

        $url = $result['data']['invoice_url'] ?? $result['data']['payment_url'] ?? null;
        if (!$result['success'] || !$url) {
            \Log::error('Paypool payment creation response', ['result' => $result]);
            $payment->update(['status' => 'failed']);
            $errorMsg = $result['error'] ?? 'Failed to create payment. Please try again.';
            if (isset($result['data']) && !$url) {
                $errorMsg = 'Payment created but payment URL is missing. Please contact support.';
            }
            return back()->withErrors([
                'payment' => $errorMsg
            ]);
        }

        // Update payment with invoice/payment URL
        $payment->update([
            'invoice_url' => $url,
            'expired_at' => $result['data']['expired_at'] ?? now()->addHours(24),
        ]);

        // Redirect to Paypool invoice/payment page
        return redirect($url);
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
        $externalId = $request->query('external_id') ?? $request->query('order_id');

        \Log::info('Payment success callback query received', [
            'external_id' => $request->query('external_id'),
            'order_id' => $request->query('order_id'),
            'status_code' => $request->query('status_code'),
            'transaction_status' => $request->query('transaction_status'),
            'resolved_external_id' => $externalId,
        ]);
        
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

        // Fallback sync in case webhook payload format changed or was delayed.
        try {
            $gateway = app(PaypoolService::class)->getPayment($externalId);
            if (($gateway['success'] ?? false) && isset($gateway['data']) && is_array($gateway['data'])) {
                $paymentData = $gateway['data'];
                $gatewayStatus = strtolower((string) ($paymentData['status'] ?? $paymentData['payment_status'] ?? $paymentData['transaction_status'] ?? ''));

                $statusMap = [
                    'settlement' => 'paid',
                    'capture' => 'paid',
                    'paid' => 'paid',
                    'settled' => 'paid',
                    'manual_paid' => 'paid',
                    'success' => 'paid',
                    'completed' => 'paid',
                    'expired' => 'expired',
                    'failed' => 'failed',
                    'deny' => 'failed',
                    'cancel' => 'failed',
                    'pending' => 'pending',
                ];

                if ($gatewayStatus !== '') {
                    $localStatus = $statusMap[$gatewayStatus] ?? $gatewayStatus;
                    $payment->update([
                        'status' => $localStatus,
                        'payment_method' => $paymentData['payment_method'] ?? $payment->payment_method,
                        'paid_at' => $localStatus === 'paid' ? ($paymentData['paid_at'] ?? $payment->paid_at ?? now()) : $payment->paid_at,
                    ]);

                    if ($localStatus === 'paid' && $payment->user) {
                        $months = 1;
                        if (isset($payment->metadata['months']) && is_numeric($payment->metadata['months'])) {
                            $months = max(1, (int) $payment->metadata['months']);
                        }

                        $payment->user->update([
                            'subscription_tier' => $payment->tier,
                            'subscription_active' => true,
                            'subscription_expires_at' => now()->addMonths($months),
                        ]);
                    }

                    $payment->refresh();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Payment success sync failed', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);
        }

        return view('dashboard.subscription.payment-success', compact('payment'));
    }

    /**
     * Payment failed callback
     */
    public function paymentFailed(Request $request)
    {
        $externalId = $request->query('external_id') ?? $request->query('order_id');

        \Log::info('Payment failed callback query received', [
            'external_id' => $request->query('external_id'),
            'order_id' => $request->query('order_id'),
            'status_code' => $request->query('status_code'),
            'transaction_status' => $request->query('transaction_status'),
            'resolved_external_id' => $externalId,
        ]);
        
        $payment = null;
        if ($externalId) {
            $payment = SubscriptionPayment::where('external_id', $externalId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('dashboard.subscription.payment-failed', compact('payment'));
    }
}
