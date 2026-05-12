@extends('layouts.app')

@section('content')
<div class="stats-container">
    <div class="stats-header">
        <div>
            <h1>📊 Plan Statistics</h1>
            <p>View subscription distribution and user analytics</p>
        </div>
        <a href="{{ route('admin.subscription-plans.index') }}" class="stats-back-btn">← Back to Plans</a>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid">
        @foreach ($stats as $planName => $count)
            <div class="stats-card stats-card-{{ $planName }}">
                <div class="stats-card-label">{{ ucfirst($planName) }} Plan</div>
                <div class="stats-card-value">{{ $count }}</div>
                <div class="stats-card-unit">{{ $count == 1 ? 'user' : 'users' }}</div>
            </div>
        @endforeach
    </div>

    <!-- Summary Card -->
    <div class="stats-summary-card">
        <h2 style="margin-bottom: 2rem;">📈 Plan Distribution</h2>
        
        <div class="stats-breakdown-grid">
            <div class="stats-breakdown-item stats-breakdown-free">
                <div class="stats-breakdown-name">🆓 Free Tier</div>
                <div class="stats-breakdown-count">{{ $stats['free'] }}</div>
                <div class="stats-breakdown-percentage">{{ round($stats['free'] / max(array_sum($stats), 1) * 100, 1) }}% of all users</div>
            </div>

            <div class="stats-breakdown-item stats-breakdown-starter">
                <div class="stats-breakdown-name">⭐ Starter Tier</div>
                <div class="stats-breakdown-count">{{ $stats['starter'] }}</div>
                <div class="stats-breakdown-percentage">{{ round($stats['starter'] / max(array_sum($stats), 1) * 100, 1) }}% of all users</div>
            </div>

            <div class="stats-breakdown-item stats-breakdown-professional">
                <div class="stats-breakdown-name">💎 Professional Tier</div>
                <div class="stats-breakdown-count">{{ $stats['professional'] }}</div>
                <div class="stats-breakdown-percentage">{{ round($stats['professional'] / max(array_sum($stats), 1) * 100, 1) }}% of all users</div>
            </div>

            <div class="stats-breakdown-item stats-breakdown-enterprise">
                <div class="stats-breakdown-name">👑 Enterprise Tier</div>
                <div class="stats-breakdown-count">{{ $stats['enterprise'] }}</div>
                <div class="stats-breakdown-percentage">{{ round($stats['enterprise'] / max(array_sum($stats), 1) * 100, 1) }}% of all users</div>
            </div>
        </div>

        <!-- Distribution Chart -->
        <div class="stats-distribution-chart">
            <h3>📊 User Distribution by Plan</h3>
            
            @php
                $icons = [
                    'free' => '🆓',
                    'starter' => '⭐',
                    'professional' => '💎',
                    'enterprise' => '👑'
                ];
            @endphp
            
            @foreach ($stats as $planName => $count)
                <div class="stats-distribution-bar">
                    <div class="stats-distribution-label">
                        <span class="stats-distribution-name">{{ $icons[$planName] }} {{ ucfirst($planName) }}</span>
                        <span class="stats-distribution-percent">{{ round($count / max(array_sum($stats), 1) * 100, 1) }}%</span>
                    </div>
                    <div class="stats-progress-bar stats-progress-{{ $planName }}">
                        <div class="stats-progress-fill" style="width: {{ round($count / max(array_sum($stats), 1) * 100, 1) }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
