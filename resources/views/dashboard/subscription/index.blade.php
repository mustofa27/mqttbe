@extends('layouts.app')

@section('title', 'My Subscription')

@section('content')
<div class="subscription-header">
    <h1>My Subscription</h1>
    <div class="subscription-tier-badge tier-{{ $user->subscription_tier }}">
        {{ strtoupper($user->subscription_tier) }} Plan
    </div>
    @if($user->subscription_expires_at)
        <p>
            @if($user->hasActiveSubscription())
                Renews on {{ $user->subscription_expires_at->format('F j, Y') }}
            @else
                Expired on {{ $user->subscription_expires_at->format('F j, Y') }}
            @endif
        </p>
    @endif
</div>

@if($user->subscription_expires_at && $user->subscription_expires_at->isFuture() && now()->diffInDays($user->subscription_expires_at, false) <= 7)
    <div class="expiry-notice">
        ⚠️ Your subscription will expire in {{ (int) now()->diffInDays($user->subscription_expires_at, false) }} day(s).
        Please renew to continue using premium features.
    </div>
@endif

@if(!$user->hasActiveSubscription() && $user->subscription_tier !== 'free')
    <div class="expiry-notice expired">
        ❌ Your subscription has expired. You've been downgraded to the Free plan. Please upgrade to restore your premium features.
    </div>
@endif

<h2>Usage Statistics</h2>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-title">Projects</div>
        <div class="stat-value">{{ $usage['projects']['current'] }}</div>
        <div class="stat-limit">
            @if($usage['projects']['unlimited'])
                Unlimited
            @else
                of {{ $usage['projects']['limit'] }} allowed
            @endif
        </div>
        @if(!$usage['projects']['unlimited'])
            <div class="progress-bar">
                @php
                    $percentage = ($usage['projects']['current'] / $usage['projects']['limit']) * 100;
                    $colorClass = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : '');
                @endphp
                <div class="progress-fill {{ $colorClass }}" style="width: {{ min($percentage, 100) }}%"></div>
            </div>
        @endif
    </div>

    <div class="stat-card">
        <div class="stat-title">Total Devices</div>
        <div class="stat-value">{{ $usage['devices']['current'] }}</div>
        <div class="stat-limit">
            @if($usage['devices']['unlimited'])
                Unlimited
            @else
                Max {{ $usage['devices']['limit_per_project'] }} per project
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Total Topics</div>
        <div class="stat-value">{{ $usage['topics']['current'] }}</div>
        <div class="stat-limit">
            @if($usage['topics']['unlimited'])
                Unlimited
            @else
                Max {{ $usage['topics']['limit_per_project'] }} per project
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Rate Limit</div>
        <div class="stat-value">
            @if($currentLimits['rate_limit_per_hour'] == -1)
                ∞
            @else
                {{ number_format($currentLimits['rate_limit_per_hour']) }}
            @endif
        </div>
        <div class="stat-limit">messages/hour</div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Monthly Messages</div>
        <div class="stat-value">{{ number_format($usage['monthly_messages']['current']) }}</div>
        <div class="stat-limit">
            @if($usage['monthly_messages']['unlimited'])
                High Fair Use
            @else
                of {{ number_format($usage['monthly_messages']['limit']) }}
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Active API Keys</div>
        <div class="stat-value">{{ $usage['api_keys']['current'] }}</div>
        <div class="stat-limit">
            @if($usage['api_keys']['unlimited'])
                High Fair Use
            @else
                of {{ $usage['api_keys']['limit'] }}
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Dashboard Widgets</div>
        <div class="stat-value">{{ $usage['widgets']['current'] }}</div>
        <div class="stat-limit">
            @if($usage['widgets']['unlimited'])
                High Fair Use
            @else
                of {{ $usage['widgets']['limit'] }}
            @endif
        </div>
    </div>
</div>

<div class="features-section">
    <h2>Your Features</h2>
    <div class="features-grid">
        <div class="feature-item {{ $currentLimits['analytics_enabled'] ? 'feature-enabled' : 'feature-disabled' }}">
            {{ $currentLimits['analytics_enabled'] ? '✅' : '❌' }} Analytics Dashboard
        </div>
        <div class="feature-item {{ !empty($currentLimits['advanced_analytics_enabled']) ? 'feature-enabled' : 'feature-disabled' }}">
            {{ !empty($currentLimits['advanced_analytics_enabled']) ? '✅' : '❌' }} Advanced Dashboard
        </div>
        <div class="feature-item {{ $currentLimits['webhooks_enabled'] ? 'feature-enabled' : 'feature-disabled' }}">
            {{ $currentLimits['webhooks_enabled'] ? '✅' : '❌' }} Webhooks
        </div>
        <div class="feature-item {{ $currentLimits['api_access'] ? 'feature-enabled' : 'feature-disabled' }}">
            {{ $currentLimits['api_access'] ? '✅' : '❌' }} API Access
        </div>
        <div class="feature-item {{ $currentLimits['priority_support'] ? 'feature-enabled' : 'feature-disabled' }}">
            {{ $currentLimits['priority_support'] ? '✅' : '❌' }} Priority Support
        </div>
        <div class="feature-item">
            📊 Data Retention:
            @if($currentLimits['data_retention_days'] == -1)
                Unlimited
            @else
                {{ $currentLimits['data_retention_days'] }} days
            @endif
        </div>
    </div>

    <div class="action-buttons">
        @if($user->subscription_tier === 'free' || !$user->hasActiveSubscription())
            <a href="{{ route('subscription.upgrade') }}" class="btn-upgrade">
                🚀 Upgrade Plan
            </a>
        @elseif($user->subscription_tier !== 'enterprise')
            <a href="{{ route('subscription.upgrade') }}" class="btn-upgrade">
                ⬆️ Upgrade to Higher Tier
            </a>
        @endif

        @if($user->subscription_tier !== 'free' && $user->hasActiveSubscription())
            <form method="POST" action="{{ route('subscription.cancel') }}" style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to cancel your subscription? You will lose access to premium features.');">
                @csrf
                <button type="submit" class="btn-cancel">Cancel Subscription</button>
            </form>
        @endif
    </div>
</div>

@if(isset($activeAddons) && $activeAddons->count() > 0)
<div class="features-section">
    <h2>Active Add-ons</h2>
    <table class="subscription-table">
        <thead>
            <tr>
                <th>Add-on</th>
                <th>Code</th>
                <th>Quantity</th>
                <th>Unit Type</th>
                <th>Expires</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activeAddons as $addon)
                <tr>
                    <td>{{ $addon->addon?->name ?? $addon->addon_code }}</td>
                    <td>{{ $addon->addon_code }}</td>
                    <td>{{ $addon->quantity }}</td>
                    <td>{{ \App\Models\SubscriptionAddon::labelFor($addon->addon?->unit_type) }}</td>
                    <td>{{ $addon->expires_at ? \Carbon\Carbon::parse($addon->expires_at)->format('d M Y') : 'No Expiry' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="features-section">
    <h2>Compare Plans</h2>
    <table class="subscription-table">
        <thead>
            <tr>
                <th>Feature</th>
                <th>Free</th>
                <th>Starter</th>
                <th>Professional</th>
                <th>Enterprise</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allPlans as $tier => $limits)
                @if($tier === array_key_first($allPlans))
                    <tr>
                        <td>Max Projects</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_projects'] == -1 ? 'Unlimited' : $l['max_projects'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Devices per Project</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_devices_per_project'] == -1 ? 'Unlimited' : $l['max_devices_per_project'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Topics per Project</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_topics_per_project'] == -1 ? 'Unlimited' : $l['max_topics_per_project'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Monthly Messages</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_monthly_messages'] == -1 ? 'Unlimited' : number_format($l['max_monthly_messages']) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>API Keys</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_api_keys'] == -1 ? 'Unlimited' : $l['max_api_keys'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Webhooks per Project</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_webhooks_per_project'] == -1 ? 'Unlimited' : $l['max_webhooks_per_project'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Dashboard Widgets</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['max_advance_dashboard_widgets'] == -1 ? 'Unlimited' : $l['max_advance_dashboard_widgets'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>API RPM</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['api_rpm'] == -1 ? 'Unlimited' : number_format($l['api_rpm']) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Data Retention</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['data_retention_days'] == -1 ? 'Unlimited' : $l['data_retention_days'] . ' days' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Analytics</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['analytics_enabled'] ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Advanced Dashboard</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ !empty($l['advanced_analytics_enabled']) ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Webhooks Access</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ $l['webhooks_enabled'] ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>API Access</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ !empty($l['api_access']) ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Priority Support</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                {{ !empty($l['priority_support']) ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Secure Connection (SSL/TLS)</td>
                        @foreach($allPlans as $t => $l)
                            <td class="{{ $user->subscription_tier === $t ? 'table-tier-highlight' : '' }}">
                                ✅
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

@if($payments->count() > 0)
<div class="features-section">
    <h2>Payment History</h2>
    <table class="subscription-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                <td>{{ ucfirst($payment->tier) }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>
                    @if($payment->status === 'paid')
                        <span class="status-badge status-paid">✓ Paid</span>
                    @elseif($payment->status === 'pending')
                        <span class="status-badge status-pending">⏳ Pending</span>
                    @elseif($payment->status === 'expired')
                        <span class="status-badge status-expired">⌛ Expired</span>
                    @else
                        <span class="status-badge status-failed">✗ Failed</span>
                    @endif
                </td>
                <td>
                    @if($payment->status === 'pending' && $payment->invoice_url)
                        <a href="{{ $payment->invoice_url }}" target="_blank" class="payment-link">
                            Continue Payment →
                        </a>
                    @elseif($payment->status === 'paid')
                        <span style="color: #999; font-size: 0.9rem;">Completed</span>
                    @else
                        <span style="color: #999; font-size: 0.9rem;">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
