<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Topic;
use App\Models\SubscriptionPayment;
use App\Models\ApiKey;
use App\Models\AdvanceDashboardWidget;
use App\Models\Webhook;
use App\Models\UsageLog;
use App\Models\SubscriptionAddon;
use App\Models\UserAddon;
use App\Services\SubscriptionBillingService;
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

        $usage['api_keys'] = [
            'current' => ApiKey::where('user_id', $user->id)->where('is_active', true)->count(),
            'limit' => (int) ($currentLimits['max_api_keys'] ?? 0),
            'unlimited' => SubscriptionPlan::isUnlimited((int) ($currentLimits['max_api_keys'] ?? 0)),
        ];

        $usage['webhooks'] = [
            'current' => Webhook::whereHas('project', fn ($query) => $query->where('user_id', $user->id))
                ->where('active', true)
                ->count(),
            'limit' => (int) ($currentLimits['max_webhooks_per_project'] ?? 0),
            'unlimited' => SubscriptionPlan::isUnlimited((int) ($currentLimits['max_webhooks_per_project'] ?? 0)),
        ];

        $usage['widgets'] = [
            'current' => AdvanceDashboardWidget::where('user_id', $user->id)->count(),
            'limit' => (int) ($currentLimits['max_advance_dashboard_widgets'] ?? 0),
            'unlimited' => SubscriptionPlan::isUnlimited((int) ($currentLimits['max_advance_dashboard_widgets'] ?? 0)),
        ];

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthlyMessages = (int) UsageLog::where('user_id', $user->id)
            ->where('period_type', 'hour')
            ->whereBetween('period_start', [$monthStart, $monthEnd])
            ->sum('message_count');

        $usage['monthly_messages'] = [
            'current' => $monthlyMessages,
            'limit' => (int) ($currentLimits['max_monthly_messages'] ?? 0),
            'unlimited' => SubscriptionPlan::isUnlimited((int) ($currentLimits['max_monthly_messages'] ?? 0)),
        ];

        $activeAddons = UserAddon::with('addon')
            ->where('user_id', $user->id)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->whereHas('addon', fn ($query) => $query->where('active', true))
            ->orderByDesc('created_at')
            ->get();

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

        return view('dashboard.subscription.index', compact('user', 'currentLimits', 'usage', 'allPlans', 'payments', 'activeAddons'));
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

        $addons = SubscriptionAddon::where('active', true)
            ->orderBy('price')
            ->get();

        return view('dashboard.subscription.upgrade', compact('user', 'currentTier', 'plans', 'addons'));
    }

    /**
     * Process subscription upgrade (placeholder - integrate with payment gateway).
     */
    public function processUpgrade(Request $request, PaypoolService $paypool)
    {
        $validated = $request->validate([
            'tier' => 'nullable|in:starter,professional,enterprise',
            'months' => 'nullable|integer|min:1|max:36',
            'addon_codes' => 'nullable|array|min:1',
            'addon_codes.*' => 'string|exists:subscription_addons,code',
        ]);

        $user = auth()->user();
        $tier = $validated['tier'] ?? null;
        $addonCodes = $validated['addon_codes'] ?? [];

        if (!$tier && empty($addonCodes)) {
            return back()->withErrors([
                'upgrade' => 'Please select a plan or at least one add-on.',
            ]);
        }

        $months = isset($validated['months']) ? (int)$validated['months'] : 1;

        $lineItems = [];
        $amount = 0;

        if ($tier) {
            $plan = SubscriptionPlan::where('tier', $tier)->firstOrFail();
            $planAmount = (int) $plan->price * $months;
            $amount += $planAmount;

            $lineItems[] = [
                'type' => 'base',
                'description' => 'ICMQTT ' . ucfirst($tier) . ' subscription (' . $months . ' month' . ($months > 1 ? 's' : '') . ')',
                'amount' => $planAmount,
                'currency' => 'IDR',
                'period_start' => now()->toDateTimeString(),
                'period_end' => now()->copy()->addMonths($months)->toDateTimeString(),
            ];
        }

        $addonItems = [];
        if (!empty($addonCodes)) {
            $requestedAddonCounts = array_count_values($addonCodes);
            $addons = SubscriptionAddon::query()
                ->whereIn('code', array_keys($requestedAddonCounts))
                ->where('active', true)
                ->get()
                ->keyBy('code');

            foreach ($requestedAddonCounts as $code => $quantity) {
                $addon = $addons->get($code);
                if (!$addon) {
                    continue;
                }

                $multiplier = $addon->is_recurring ? $months : 1;
                $addonAmount = (int) $addon->price * $quantity * $multiplier;
                $amount += $addonAmount;

                $addonItems[] = [
                    'code' => (string) $addon->code,
                    'quantity' => (int) $quantity,
                    'is_recurring' => (bool) $addon->is_recurring,
                ];

                $lineItems[] = [
                    'type' => 'addon',
                    'description' => (string) $addon->name . ' x' . $quantity,
                    'amount' => $addonAmount,
                    'currency' => 'IDR',
                    'period_start' => now()->toDateTimeString(),
                    'period_end' => $addon->is_recurring ? now()->copy()->addMonths($months)->toDateTimeString() : null,
                ];
            }
        }

        if ($amount <= 0) {
            return back()->withErrors([
                'upgrade' => 'Unable to calculate payment amount for selected plan/add-ons.',
            ]);
        }

        // Generate unique external ID with random string
        $referenceTier = strtoupper($tier ?? $user->subscription_tier ?? 'ADDON');
        $externalId = 'SUB-' . $user->id . '-' . $referenceTier . '-' . time() . '-' . Str::random(6);

        // Create payment record
        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'external_id' => $externalId,
            'tier' => $tier ?? ($user->subscription_tier ?? 'free'),
            'addon_code' => count($addonItems) === 1 ? $addonItems[0]['code'] : null,
            'amount' => $amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'tier' => $tier,
                'months' => $months,
                'is_base_plan' => (bool) $tier,
                'addon_items' => $addonItems,
                'line_items' => $lineItems,
            ],
        ]);

        // Create payment via Paypool
        $result = $paypool->createPayment([
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => 'IDR',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'description' => $tier
                ? 'ICMQTT ' . ucfirst($tier) . ' Subscription (' . $months . ' month' . ($months > 1 ? 's' : '') . ')'
                : 'ICMQTT Add-on purchase (' . $months . ' month' . ($months > 1 ? 's' : '') . ')',
            'metadata' => [
                'user_id' => $user->id,
                'tier' => $tier,
                'months' => $months,
                'payment_id' => $payment->id,
                'is_base_plan' => (bool) $tier,
                'addon_items' => $addonItems,
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

        $user->userAddons()
            ->where('active', true)
            ->update([
                'active' => false,
                'expires_at' => now(),
            ]);

        return redirect()
            ->route('subscription.index')
            ->with('success', 'Subscription cancelled. You are now on the free plan.');
    }

    /**
     * Payment success callback
     */
    public function paymentSuccess(Request $request, SubscriptionBillingService $billingService)
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
                $gatewayData = $gateway['data'];
                $paymentData = [];

                if (is_array($gatewayData['payment'] ?? null)) {
                    $paymentData = $gatewayData['payment'];
                } elseif (is_array($gatewayData['transaction'] ?? null)) {
                    $paymentData = $gatewayData['transaction'];
                } else {
                    $paymentData = $gatewayData;
                }

                $gatewayStatus = strtolower((string) ($paymentData['status']
                    ?? $paymentData['payment_status']
                    ?? $paymentData['transaction_status']
                    ?? $gatewayData['status']
                    ?? $gatewayData['payment_status']
                    ?? $gatewayData['transaction_status']
                    ?? ''));

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
                        'payment_method' => $paymentData['payment_method'] ?? $gatewayData['payment_method'] ?? $payment->payment_method,
                        'paid_at' => $localStatus === 'paid'
                            ? ($paymentData['paid_at'] ?? $gatewayData['paid_at'] ?? $payment->paid_at ?? now())
                            : $payment->paid_at,
                    ]);

                    if ($localStatus === 'paid' && $payment->user) {
                        $billingService->applySuccessfulPayment($payment);
                    }

                    $payment->refresh();
                } else {
                    \Log::warning('Payment success sync could not resolve gateway status', [
                        'external_id' => $externalId,
                        'gateway_data_keys' => array_keys($gatewayData),
                        'payment_data_keys' => array_keys($paymentData),
                    ]);
                }
            } else {
                \Log::warning('Payment success sync gateway lookup failed', [
                    'external_id' => $externalId,
                    'gateway_result' => $gateway,
                ]);
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
