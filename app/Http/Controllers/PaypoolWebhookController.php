<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypoolWebhookController extends Controller
{
    /**
     * Handle Paypool webhook
     */
    public function handle(Request $request, SubscriptionBillingService $billingService)
    {
        Log::info('Paypool webhook received', $request->all());

        $event = $request->input('event')
            ?? $request->input('event_type')
            ?? $request->input('type');
        $event = is_string($event) ? strtolower(trim($event)) : null;

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

        $externalId = $paymentData['external_id']
            ?? $paymentData['externalId']
            ?? $paymentData['order_id']
            ?? $paymentData['reference_id']
            ?? $paymentData['reference']
            ?? $request->input('external_id')
            ?? $request->input('externalId')
            ?? $request->input('order_id');

        $status = $paymentData['status']
            ?? $paymentData['payment_status']
            ?? $paymentData['transaction_status']
            ?? $paymentData['state']
            ?? $request->input('status')
            ?? $request->input('transaction_status')
            ?? $request->input('payment_status');

        if ($event && !in_array($event, $supportedEvents, true)) {
            Log::info('Webhook event not in known list; attempting best-effort processing', [
                'event' => $event,
                'external_id' => $externalId,
                'status' => $status,
            ]);
        }

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
            $billingService->applySuccessfulPayment($payment);

            Log::info('User subscription upgraded', [
                'user_id' => $payment->user_id,
                'tier' => $payment->tier,
                'months' => $payment->metadata['months'] ?? 1,
                'addon_items' => $payment->metadata['addon_items'] ?? [],
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
}
