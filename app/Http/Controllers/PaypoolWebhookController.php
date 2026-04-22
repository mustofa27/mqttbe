<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypoolWebhookController extends Controller
{
    /**
     * Handle Paypool webhook
     */
    public function handle(Request $request)
    {
        Log::info('Paypool webhook received', $request->all());

        $event = $request->input('event')
            ?? $request->input('event_type')
            ?? $request->input('type');

        $paymentData = $request->input('payment');
        if (!is_array($paymentData)) {
            // Some providers send payment fields at the root payload level.
            $paymentData = $request->all();
        }

        $supportedEvents = [
            'payment.updated',
            'payment_update',
            'payment.paid',
            'payment.settled',
            'manual_paid',
            'manual-settled',
            'transaction_status',
        ];

        if ($event && !in_array($event, $supportedEvents, true)) {
            Log::info('Webhook event ignored', ['event' => $event]);
            return response()->json(['message' => 'Event not handled'], 200);
        }

        $externalId = $paymentData['external_id']
            ?? $paymentData['order_id']
            ?? $request->input('external_id')
            ?? $request->input('order_id');

        $status = $paymentData['status']
            ?? $paymentData['payment_status']
            ?? $paymentData['transaction_status']
            ?? $request->input('status')
            ?? $request->input('transaction_status');

        if (!$status && in_array($event, ['manual_paid', 'payment.paid', 'payment.settled'], true)) {
            $status = 'paid';
        }

        // Find payment record
        $payment = $externalId ? SubscriptionPayment::where('external_id', $externalId)->first() : null;

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['external_id' => $externalId]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Map Midtrans/Paypool statuses to local statuses
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
        $statusKey = is_string($status) ? strtolower(trim($status)) : null;
        $localStatus = $statusKey ? ($statusMap[$statusKey] ?? $statusKey) : 'pending';

        // Update payment record
        $payment->update([
            'status' => $localStatus,
            'payment_method' => $paymentData['payment_method'] ?? $payment->payment_method,
            'paid_at' => $localStatus === 'paid' ? ($paymentData['paid_at'] ?? $payment->paid_at ?? now()) : $payment->paid_at,
        ]);

        // If payment is successful, upgrade user subscription
        if ($localStatus === 'paid' && $payment->user) {
            $months = 1;
            if (isset($payment->metadata['months']) && is_numeric($payment->metadata['months'])) {
                $months = (int) $payment->metadata['months'];
                if ($months < 1) $months = 1;
            }
            $payment->user->update([
                'subscription_tier' => $payment->tier,
                'subscription_active' => true,
                'subscription_expires_at' => now()->addMonths($months),
            ]);

            $this->ensureSystemListenerDevicesForAnalytics($payment->user, (string) $payment->tier);

            Log::info('User subscription upgraded', [
                'user_id' => $payment->user_id,
                'tier' => $payment->tier,
                'months' => $months,
            ]);
        }

        // Log and handle other statuses as needed
        if (!in_array($localStatus, ['paid', 'expired', 'failed'])) {
            Log::info('Unhandled payment status received', [
                'external_id' => $externalId,
                'status' => $status,
                'local_status' => $localStatus,
            ]);
        }

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    private function ensureSystemListenerDevicesForAnalytics(User $user, string $tier): void
    {
        $plan = SubscriptionPlan::where('tier', $tier)->first();
        if (!$plan || !$plan->analytics_enabled) {
            return;
        }

        foreach ($user->projects()->where('active', true)->get() as $project) {
            $hash = substr(md5((string) $project->id), 0, 4);
            $deviceIdWithHash = 'system_listener-' . $hash;

            $device = Device::firstOrCreate(
                [
                    'project_id' => (int) $project->id,
                    'device_id' => $deviceIdWithHash,
                ],
                [
                    'type' => 'dashboard',
                    'active' => true,
                ]
            );

            if (!$device->active) {
                $device->active = true;
                $device->save();
            }
        }
    }
}
