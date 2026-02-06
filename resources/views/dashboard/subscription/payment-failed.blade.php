@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<style>
    .payment-status-card {
        max-width: 600px;
        margin: 3rem auto;
        background: white;
        padding: 3rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
    }

    .status-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .status-title {
        font-size: 2rem;
        font-weight: bold;
        color: #dc3545;
        margin-bottom: 1rem;
    }

    .status-message {
        color: #666;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .payment-details {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
        text-align: left;
    }

    .payment-details-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .payment-details-row:last-child {
        border-bottom: none;
    }

    .payment-details-label {
        color: #666;
        font-weight: 600;
    }

    .payment-details-value {
        color: #333;
    }
</style>

<div class="payment-status-card">
    <div class="status-icon">❌</div>
    <h1 class="status-title">Payment Failed</h1>
    
    <p class="status-message">
        Unfortunately, your payment could not be processed. This may happen due to:
    </p>

    <ul style="text-align: left; margin: 1rem auto; max-width: 400px; color: #666;">
        <li>Payment timeout or cancellation</li>
        <li>Insufficient funds</li>
        <li>Technical issues with payment gateway</li>
        <li>Invalid payment method</li>
    </ul>

    @if($payment)
    <div class="payment-details">
        <div class="payment-details-row">
            <span class="payment-details-label">Plan:</span>
            <span class="payment-details-value">{{ ucfirst($payment->tier) }}</span>
        </div>
        <div class="payment-details-row">
            <span class="payment-details-label">Amount:</span>
            <span class="payment-details-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
        </div>
        <div class="payment-details-row">
            <span class="payment-details-label">Payment ID:</span>
            <span class="payment-details-value">{{ $payment->external_id }}</span>
        </div>
        <div class="payment-details-row">
            <span class="payment-details-label">Status:</span>
            <span class="payment-details-value">
                <span style="color: #dc3545; font-weight: bold;">
                    {{ ucfirst($payment->status) }}
                </span>
            </span>
        </div>
    </div>
    @endif

    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
        <a href="{{ route('subscription.upgrade') }}" class="btn btn-primary">
            Try Again
        </a>
        <a href="{{ route('subscription.index') }}" class="btn btn-secondary">
            Back to Subscription
        </a>
    </div>

    <p style="color: #999; font-size: 0.9rem; margin-top: 2rem;">
        Need help? Contact our support team.
    </p>
</div>
@endsection
