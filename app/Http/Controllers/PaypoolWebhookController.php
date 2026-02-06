<?php

namespace App\Http\Controllers;

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

        $event = $request->input('event');
        $paymentData = $request->input('payment');

        if ($event !== 'payment.updated') {
            return response()->json(['message' => 'Event not handled'], 200);
        }

        $externalId = $paymentData['external_id'];
        $status = $paymentData['status'];

        // Find payment record
        $payment = SubscriptionPayment::where('external_id', $externalId)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['external_id' => $externalId]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Update payment record
        $payment->update([
            'status' => $status,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'paid_at' => $status === 'paid' ? ($paymentData['paid_at'] ?? now()) : null,
        ]);

        // If payment is successful, upgrade user subscription
        if ($status === 'paid' && $payment->user) {
            $payment->user->update([
                'subscription_tier' => $payment->tier,
                'subscription_active' => true,
                'subscription_expires_at' => now()->addMonth(), // Monthly billing
            ]);

            Log::info('User subscription upgraded', [
                'user_id' => $payment->user_id,
                'tier' => $payment->tier,
            ]);
        }

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }
}
