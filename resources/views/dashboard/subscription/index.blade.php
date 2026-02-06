@extends('layouts.app')

@section('title', 'My Subscription')

@section('content')
<style>
    .subscription-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .subscription-tier-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .tier-free { background: #6c757d; }
    .tier-starter { background: #17a2b8; }
    .tier-professional { background: #667eea; }
    .tier-enterprise { background: #764ba2; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stat-title {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .stat-limit {
        font-size: 0.9rem;
        color: #999;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: #667eea;
        transition: width 0.3s;
    }

    .progress-fill.warning {
        background: #ffc107;
    }

    .progress-fill.danger {
        background: #dc3545;
    }

    .features-section {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .feature-enabled {
        color: #28a745;
    }

    .feature-disabled {
        color: #dc3545;
        opacity: 0.6;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-upgrade {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-upgrade:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-cancel {
        background: #dc3545;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    .expiry-notice {
        background: #fff3cd;
        color: #856404;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
</style>

<div class="subscription-header">
    <h1 style="margin: 0; font-size: 2rem;">My Subscription</h1>
    <div class="subscription-tier-badge tier-{{ $user->subscription_tier }}">
        {{ strtoupper($user->subscription_tier) }} Plan
    </div>
    @if($user->subscription_expires_at)
        <p style="margin-top: 1rem; opacity: 0.9;">
            @if($user->hasActiveSubscription())
                Renews on {{ $user->subscription_expires_at->format('F j, Y') }}
            @else
                Expired on {{ $user->subscription_expires_at->format('F j, Y') }}
            @endif
        </p>
    @endif
</div>

@if($user->subscription_expires_at && $user->subscription_expires_at->diffInDays(now()) <= 7 && $user->subscription_expires_at->isFuture())
    <div class="expiry-notice">
        ⚠️ Your subscription will expire in {{ $user->subscription_expires_at->diffInDays(now()) }} days.
        Please renew to continue using premium features.
    </div>
@endif

@if(!$user->hasActiveSubscription() && $user->subscription_tier !== 'free')
    <div class="expiry-notice" style="background: #f8d7da; color: #721c24;">
        ❌ Your subscription has expired. You've been downgraded to the Free plan. Please upgrade to restore your premium features.
    </div>
@endif

<h2 style="margin-bottom: 1rem;">Usage Statistics</h2>
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
</div>

<div class="features-section">
    <h2 style="margin-bottom: 1rem;">Your Features</h2>
    <div class="features-grid">
        <div class="feature-item {{ $currentLimits['analytics_enabled'] ? 'feature-enabled' : 'feature-disabled' }}">
            {{ $currentLimits['analytics_enabled'] ? '✅' : '❌' }} Analytics Dashboard
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

<div class="features-section">
    <h2 style="margin-bottom: 1rem;">Compare Plans</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #e0e0e0;">
                <th style="text-align: left; padding: 1rem;">Feature</th>
                <th style="text-align: center; padding: 1rem;">Free</th>
                <th style="text-align: center; padding: 1rem;">Starter</th>
                <th style="text-align: center; padding: 1rem;">Professional</th>
                <th style="text-align: center; padding: 1rem;">Enterprise</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allPlans as $tier => $limits)
                @if($tier === array_key_first($allPlans))
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">Max Projects</td>
                        @foreach($allPlans as $t => $l)
                            <td style="text-align: center; padding: 0.75rem; {{ $user->subscription_tier === $t ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ $l['max_projects'] == -1 ? 'Unlimited' : $l['max_projects'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">Devices per Project</td>
                        @foreach($allPlans as $t => $l)
                            <td style="text-align: center; padding: 0.75rem; {{ $user->subscription_tier === $t ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ $l['max_devices_per_project'] == -1 ? 'Unlimited' : $l['max_devices_per_project'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">Topics per Project</td>
                        @foreach($allPlans as $t => $l)
                            <td style="text-align: center; padding: 0.75rem; {{ $user->subscription_tier === $t ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ $l['max_topics_per_project'] == -1 ? 'Unlimited' : $l['max_topics_per_project'] }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">Analytics</td>
                        @foreach($allPlans as $t => $l)
                            <td style="text-align: center; padding: 0.75rem; {{ $user->subscription_tier === $t ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ $l['analytics_enabled'] ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">Webhooks</td>
                        @foreach($allPlans as $t => $l)
                            <td style="text-align: center; padding: 0.75rem; {{ $user->subscription_tier === $t ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ $l['webhooks_enabled'] ? '✅' : '❌' }}
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
