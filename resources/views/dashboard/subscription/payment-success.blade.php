@extends('layouts.app')

@section('title', 'Payment Successful')

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
        color: #28a745;
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
    <div class="status-icon">✅</div>
    <h1 class="status-title">Payment Successful!</h1>
    
    @if($payment->status === 'paid')
        <p class="status-message">
            Your subscription has been upgraded to <strong>{{ ucfirst($payment->tier) }}</strong> plan.
            You now have access to all premium features!
        </p>
    @else
        <p class="status-message">
            Your payment is being processed. Your subscription will be activated shortly.
        </p>
    @endif

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
                <span style="color: {{ $payment->status === 'paid' ? '#28a745' : '#ffc107' }}; font-weight: bold;">
                    {{ ucfirst($payment->status) }}
                </span>
            </span>
        </div>
        @if($payment->paid_at)
        <div class="payment-details-row">
            <span class="payment-details-label">Paid At:</span>
            <span class="payment-details-value">{{ $payment->paid_at->format('d M Y, H:i') }}</span>
        </div>
        @endif
    </div>

    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
        <a href="{{ route('subscription.index') }}" class="btn btn-primary">
            View Subscription
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            Go to Dashboard
        </a>
    </div>
</div>
@endsection
