@extends('layouts.app')

@section('title', 'Usage Dashboard')

@section('content')
<div class="container">
    <div class="page-header">
        <h1 class="page-title">Welcome, {{ Auth::user()->name }}! 👋</h1>
        <p class="page-subtitle">Here's your MQTT management overview</p>
    </div>

    <div id="setupGuideCard" class="setup-guide-card">
        <div class="setup-guide-header">
            <h2>Getting Started</h2>
            <button type="button" id="dismissSetupGuide" class="setup-guide-dismiss" aria-label="Dismiss setup guide">x</button>
        </div>
        <p class="setup-guide-text">Complete this quick setup so your devices can publish and your dashboard can display live usage.</p>
        <div class="setup-guide-steps">
            <span>1. Create a project</span>
            <span>2. Register a device</span>
            <span>3. Configure topic templates</span>
            <span>4. Test publish from device</span>
        </div>
        <div class="setup-guide-actions">
            <a href="{{ route('projects.create') }}" class="btn-small">Create Project</a>
            <a href="{{ route('setup.guide') }}" class="btn-small setup-guide-secondary">View Full Guide</a>
        </div>
    </div>

    @php
        $maxProjects = $limits['max_projects'];
        $projectsAvailable = $maxProjects === -1 ? null : max(0, $maxProjects - $projectsCount);
        $projectsPercent = ($maxProjects === -1 || $maxProjects === 0)
            ? 0
            : min(100, ($projectsCount / $maxProjects) * 100);

        $maxDevicesPerProject = $limits['max_devices_per_project'];
        $devicesCapacity = ($maxProjects === -1 || $maxDevicesPerProject === -1)
            ? null
            : ($maxProjects * $maxDevicesPerProject);
        $devicesAvailable = is_null($devicesCapacity) ? null : max(0, $devicesCapacity - $devicesCount);
        $devicesPercent = (is_null($devicesCapacity) || $devicesCapacity === 0)
            ? 0
            : min(100, ($devicesCount / $devicesCapacity) * 100);

        $maxTopicsPerProject = $limits['max_topics_per_project'];
        $topicsCapacity = ($maxProjects === -1 || $maxTopicsPerProject === -1)
            ? null
            : ($maxProjects * $maxTopicsPerProject);
        $topicsAvailable = is_null($topicsCapacity) ? null : max(0, $topicsCapacity - $topicsCount);
        $topicsPercent = (is_null($topicsCapacity) || $topicsCapacity === 0)
            ? 0
            : min(100, ($topicsCount / $topicsCapacity) * 100);
    @endphp

    <div class="dashboard-grid">
        <!-- Current Hour Usage Card -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Current Hour Usage</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    {{ number_format($currentHourUsage) }}
                    @if($rateLimit !== -1)
                        / {{ number_format($rateLimit) }}
                    @else
                        / ∞
                    @endif
                </div>
                <div class="stat-label">messages</div>
                @if($rateLimit !== -1 && $currentHourUsage >= $rateLimit)
                    <div class="alert alert-warning alert-margin-top">
                        ⚠️ Rate limit reached
                    </div>
                @endif
            </div>
            <div class="stat-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $rateLimit === -1 ? 0 : min(100, ($currentHourUsage / $rateLimit) * 100) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Subscription Plan Card -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Current Plan</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="text-transform: capitalize;">{{ $user->subscription_tier ?? 'free' }}</div>
                <div class="stat-label">{{ ucfirst($user->subscription_tier ?? 'free') }} Plan</div>
                @if($user->subscription_expires_at)
                    <div class="stat-info">
                        @if($user->subscription_active && $user->subscription_expires_at->isFuture())
                            ✓ Expires: {{ $user->subscription_expires_at->format('M d, Y') }}
                        @else
                            ⚠️ Expired: {{ $user->subscription_expires_at->format('M d, Y') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Projects Count -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Projects</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($projectsCount) }}</div>
                <div class="stat-label">
                    @if($maxProjects === -1)
                        Unlimited projects
                    @else
                        {{ number_format($projectsAvailable) }} available of {{ number_format($maxProjects) }}
                    @endif
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $projectsPercent }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Devices Count -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Devices</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($devicesCount) }}</div>
                <div class="stat-label">
                    @if(is_null($devicesCapacity))
                        Unlimited device slots
                    @else
                        {{ number_format($devicesAvailable) }} available of {{ number_format($devicesCapacity) }}
                    @endif
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $devicesPercent }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Topics Count -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Topics</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($topicsCount) }}</div>
                <div class="stat-label">
                    @if(is_null($topicsCapacity))
                        Unlimited topic slots
                    @else
                        {{ number_format($topicsAvailable) }} available of {{ number_format($topicsCapacity) }}
                    @endif
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $topicsPercent }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Info -->
        <div class="stat-card">
            <div class="stat-header">
                <h3>Hourly Rate Limit</h3>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    @if($limits['rate_limit_per_hour'] === -1)
                        ∞
                    @else
                        {{ number_format($limits['rate_limit_per_hour']) }}
                    @endif
                </div>
                <div class="stat-label">messages/hour</div>
            </div>
        </div>
    </div>
    <div class="card">
        <h2>Welcome to ICMQTT</h2>
        <p>
            Manage your MQTT projects, devices, and topics from a single dashboard.
        </p>
    </div>
    <!-- Plan Limits Reference -->
    <div class="card">
        <h2>Plan Features & Limits</h2>
        <div class="limits-grid">
            <div class="limit-item">
                <span class="limit-label">Projects:</span>
                <span class="limit-value">
                    @if($limits['max_projects'] === -1) Unlimited @else {{ $limits['max_projects'] }} @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">Devices per Project:</span>
                <span class="limit-value">
                    @if($limits['max_devices_per_project'] === -1) Unlimited @else {{ $limits['max_devices_per_project'] }} @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">Topics per Project:</span>
                <span class="limit-value">
                    @if($limits['max_topics_per_project'] === -1) Unlimited @else {{ $limits['max_topics_per_project'] }} @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">Hourly Rate Limit:</span>
                <span class="limit-value">
                    @if($limits['rate_limit_per_hour'] === -1) Unlimited @else {{ number_format($limits['rate_limit_per_hour']) }} msg @endif
                </span>
            </div>
            @if($limits['data_retention_days'] !== 0)
                <div class="limit-item">
                    <span class="limit-label">Data Retention:</span>
                    <span class="limit-value">
                        @if($limits['data_retention_days'] === -1) Unlimited @else {{ $limits['data_retention_days'] }} days @endif
                    </span>
                </div>
            @endif
            <div class="limit-item">
                <span class="limit-label">Analytics:</span>
                <span class="limit-value">
                    @if($limits['analytics_enabled']) ✓ Enabled @else ✗ Disabled @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">Advanced Dashboard:</span>
                <span class="limit-value">
                    @if($limits['advanced_analytics_enabled']) ✓ Enabled @else ✗ Disabled @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">API Access:</span>
                <span class="limit-value">
                    @if($limits['api_access']) ✓ Enabled @else ✗ Disabled @endif
                </span>
            </div>
            <div class="limit-item">
                <span class="limit-label">Webhooks:</span>
                <span class="limit-value">
                    @if($limits['webhooks_enabled']) ✓ Enabled @else ✗ Disabled @endif
                </span>
            </div>
        </div>

        @if($user->subscription_tier === 'free')
            <div class="upgrade-notice">
                <p>
                    <strong>Upgrade for more features</strong><br>
                    Get access to Advanced Dashboard, webhooks, and higher rate limits.
                    <a href="{{ route('subscription.upgrade') }}" class="upgrade-link">View plans →</a>
                </p>
            </div>
        @endif
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const card = document.getElementById('setupGuideCard');
        const dismissBtn = document.getElementById('dismissSetupGuide');
        const storageKey = 'usageSetupGuideDismissed';

        if (!card || !dismissBtn) {
            return;
        }

        if (localStorage.getItem(storageKey) === '1') {
            card.style.display = 'none';
        }

        dismissBtn.addEventListener('click', function () {
            localStorage.setItem(storageKey, '1');
            card.style.display = 'none';
        });
    });
</script>
@endsection
