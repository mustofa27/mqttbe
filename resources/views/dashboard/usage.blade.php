@extends('layouts.app')

@section('title', 'Usage Dashboard')

@section('content')
<div class="container">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">Welcome, {{ Auth::user()->name }}! 👋</h1>
        <p style="color: #666;">Here's your MQTT management overview</p>
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
                    <div class="alert alert-warning" style="margin-top: 0.75rem;">
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
    <div class="card" style="text-align: center; padding: 3rem;">
        <h2>Welcome to ICMQTT</h2>
        <p style="margin: 1rem 0; color: #666;">
            Manage your MQTT projects, devices, and topics from a single dashboard.
        </p>
    </div>
    <!-- Plan Limits Reference -->
    <div class="card" style="margin-top: 2rem;">
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
            <div style="margin-top: 1.5rem; padding: 1rem; background: #f0f4ff; border-radius: 8px; border-left: 4px solid #4f46e5;">
                <p style="margin: 0; color: #2d2f33;">
                    <strong>Upgrade for more features</strong><br>
                    Get access to Advanced Dashboard, webhooks, and higher rate limits.
                    <a href="{{ route('subscription.upgrade') }}" style="color: #4f46e5; text-decoration: none; font-weight: 600;">View plans →</a>
                </p>
            </div>
        @endif
    </div>
</div>

<style>
    .page-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 2rem;
    }

    .setup-guide-card {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid #dbeafe;
        border-left: 4px solid #2563eb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .setup-guide-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .setup-guide-header h2 {
        margin: 0;
        font-size: 1.1rem;
        color: #1e3a8a;
    }

    .setup-guide-dismiss {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 1rem;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
    }

    .setup-guide-text {
        margin: 0 0 0.75rem;
        color: #334155;
    }

    .setup-guide-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.9rem;
    }

    .setup-guide-steps span {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.85rem;
        color: #334155;
    }

    .setup-guide-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .setup-guide-secondary {
        background: #0f172a;
    }

    .setup-guide-secondary:hover {
        background: #020617;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .stat-header {
        margin-bottom: 1rem;
    }

    .stat-header h3 {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-content {
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0.5rem 0;
    }

    .stat-label {
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .stat-info {
        font-size: 0.85rem;
        color: #059669;
        margin-top: 0.5rem;
    }

    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }

    .card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .card h2 {
        font-size: 1.4rem;
        margin: 0 0 1.5rem;
        color: #1f2937;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .usage-table {
        width: 100%;
        border-collapse: collapse;
    }

    .usage-table thead {
        border-bottom: 2px solid #e5e7eb;
    }

    .usage-table th {
        text-align: left;
        padding: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .usage-table td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .usage-table tbody tr:hover {
        background: #f9fafb;
    }

    .btn-small {
        display: inline-block;
        padding: 0.4rem 0.75rem;
        background: #4f46e5;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-small:hover {
        background: #4338ca;
    }

    .limits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .limit-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .limit-label {
        font-weight: 600;
        color: #6b7280;
    }

    .limit-value {
        color: #1f2937;
        font-weight: 700;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        margin: 0;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .setup-guide-actions {
            flex-direction: column;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .limits-grid {
            grid-template-columns: 1fr;
        }

        .usage-table {
            font-size: 0.9rem;
        }

        .usage-table th, .usage-table td {
            padding: 0.5rem;
        }
    }
</style>

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
