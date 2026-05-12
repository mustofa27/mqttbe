@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div class="admin-header">
        <div>
            <h1 class="admin-header-title">📋 Subscription Plans</h1>
            <p class="admin-header-subtitle">Manage and customize subscription tiers</p>
        </div>
        <a href="{{ route('admin.subscription-plans.statistics') }}" class="btn-admin-action" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            📊 Statistics
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <strong>✓ Success!</strong> {{ session('success') }}
        </div>
    @endif

    <div class="plans-grid">
        @foreach ($plans as $planName => $details)
            <div class="plan-card">
                <div class="plan-card-header plan-card-header-{{ $planName }}">
                    <h2 class="plan-card-header h2" style="margin: 0;">{{ ucfirst($planName) }}</h2>
                </div>
                
                <div class="plan-card-body">
                    <!-- Price and Main Specs -->
                    <div class="plan-details-grid">
                        <div class="plan-detail-item">
                            <div class="plan-detail-label">Price</div>
                            <div class="plan-detail-value">
                                {{ $details['price'] == 0 ? 'Free' : 'Rp ' . number_format($details['price'], 2, ',', '.') }}
                            </div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Projects</div>
                            <div class="plan-detail-value-text">{{ $details['max_projects'] == -1 ? '∞' : $details['max_projects'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Devices/Proj</div>
                            <div class="plan-detail-value-text">{{ $details['max_devices_per_project'] == -1 ? '∞' : $details['max_devices_per_project'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Topics/Proj</div>
                            <div class="plan-detail-value-text">{{ $details['max_topics_per_project'] == -1 ? '∞' : $details['max_topics_per_project'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Messages/Mo</div>
                            <div class="plan-detail-value-text">{{ $details['max_monthly_messages'] == -1 ? '∞' : number_format($details['max_monthly_messages']) }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Rate Limit</div>
                            <div class="plan-detail-value-text">{{ $details['rate_limit_per_hour'] == -1 ? '∞' : number_format($details['rate_limit_per_hour']) }}/hr</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">API Keys</div>
                            <div class="plan-detail-value-text">{{ $details['max_api_keys'] == -1 ? '∞' : $details['max_api_keys'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Webhooks</div>
                            <div class="plan-detail-value-text">{{ $details['max_webhooks_per_project'] == -1 ? '∞' : $details['max_webhooks_per_project'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Widgets</div>
                            <div class="plan-detail-value-text">{{ $details['max_advance_dashboard_widgets'] == -1 ? '∞' : $details['max_advance_dashboard_widgets'] }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">API RPM</div>
                            <div class="plan-detail-value-text">{{ $details['api_rpm'] == -1 ? '∞' : number_format($details['api_rpm']) }}</div>
                        </div>
                        <div>
                            <div class="plan-detail-label">Data Retention</div>
                            <div class="plan-detail-value-text">{{ $details['data_retention_days'] == -1 ? '∞' : $details['data_retention_days'] }} days</div>
                        </div>
                    </div>

                    <!-- Features -->
                    @if ($details['analytics_enabled'] || !empty($details['advanced_analytics_enabled']) || $details['webhooks_enabled'] || $details['api_access'] || $details['priority_support'])
                        <div class="plan-details-divider"></div>
                        <div class="plan-feature-tags">
                            @if ($details['analytics_enabled'])
                                <span class="feature-tag feature-tag-analytics">✓ Analytics</span>
                            @endif
                            @if (!empty($details['advanced_analytics_enabled']))
                                <span class="feature-tag feature-tag-advanced">✓ Advanced Dashboard</span>
                            @endif
                            @if ($details['webhooks_enabled'])
                                <span class="feature-tag feature-tag-webhooks">✓ Webhooks</span>
                            @endif
                            @if ($details['api_access'])
                                <span class="feature-tag feature-tag-api">✓ API Access</span>
                            @endif
                            @if ($details['priority_support'])
                                <span class="feature-tag feature-tag-support">✓ Priority Support</span>
                            @endif
                            <span class="feature-tag feature-tag-security">✓ SSL/TLS</span>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="plan-actions">
                        <a href="{{ route('admin.subscription-plans.edit', $planName) }}" class="plan-action-btn plan-action-edit">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('admin.subscription-plans.reset', $planName) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Reset {{ ucfirst($planName) }} to default values?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="plan-action-btn plan-action-reset" style="width: 100%;">
                                🔄 Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
