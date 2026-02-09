@extends('layouts.app')

@section('title', 'Project Usage - ' . $project->name)

@section('content')
<div class="container">
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('usage.dashboard') }}" style="color: #4f46e5; text-decoration: none; font-weight: 600;">← Back to Usage Dashboard</a>
    </div>

    <h1 class="page-title">{{ $project->name }} - Usage Details</h1>
    <p class="page-subtitle">Message usage for the past 30 days</p>

    <!-- Usage Stats -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Current Hour</div>
            <div class="stat-value">
                {{ number_format($currentHourUsage) }}
                @if($limits['rate_limit_per_hour'] !== -1)
                    / {{ number_format($limits['rate_limit_per_hour']) }}
                @else
                    / ∞
                @endif
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Total (30 days)</div>
            <div class="stat-value">{{ number_format($summary['total_usage']) }}</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Data Retention</div>
            <div class="stat-value">
                @if($limits['data_retention_days'] === -1)
                    Unlimited
                @else
                    {{ $limits['data_retention_days'] }} days
                @endif
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Period</div>
            <div class="stat-value">
                {{ $from->format('M d') }} - {{ $to->format('M d, Y') }}
            </div>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="card">
        <h3>Filter by Date Range</h3>
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- Daily Breakdown -->
    <div class="card">
        <h2>Daily Usage Breakdown</h2>
        
        @if(count($summary['daily_breakdown']) > 0)
            <div class="chart-container">
                <div style="display: flex; align-items: flex-end; gap: 8px; height: 300px; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb;">
                    @php
                        $maxUsage = max($summary['daily_breakdown']->values()->toArray());
                        $maxUsage = $maxUsage > 0 ? $maxUsage : 1;
                    @endphp
                    @foreach($summary['daily_breakdown'] as $date => $count)
                        <div class="bar-item" title="{{ $date }}: {{ number_format($count) }} messages">
                            <div class="bar" style="height: {{ ($count / $maxUsage) * 250 }}px; background: linear-gradient(180deg, #667eea, #764ba2);"></div>
                            <div class="bar-label">{{ \Carbon\Carbon::parse($date)->format('M d') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="usage-table-container" style="margin-top: 2rem;">
                <table class="usage-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Messages</th>
                            <th>% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['daily_breakdown'] as $date => $count)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                <td><strong>{{ number_format($count) }}</strong></td>
                                <td>
                                    {{ $summary['total_usage'] > 0 ? round(($count / $summary['total_usage']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #999; padding: 2rem; text-align: center;">
                No usage data for the selected period.
            </p>
        @endif
    </div>

    <!-- Topics -->
    <div class="card">
        <h2>Project Topics</h2>
        @if($project->topics->count() > 0)
            <div class="topics-list">
                @foreach($project->topics as $topic)
                    <div class="topic-item">
                        <strong>{{ $topic->name }}</strong>
                        <small style="color: #999;">{{ $topic->topic_name }}</small>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #999;">No topics created yet.</p>
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

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
    }

    .card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        margin-bottom: 2rem;
    }

    .card h2, .card h3 {
        margin: 0 0 1.5rem;
        color: #1f2937;
    }

    .card h3 {
        font-size: 1.1rem;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.4rem;
        color: #2d2f33;
    }

    .form-group input {
        padding: 0.6rem;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 1rem;
    }

    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 0.6rem 1.25rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    .chart-container {
        margin: 1rem 0;
    }

    .bar-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .bar {
        width: 100%;
        border-radius: 4px 4px 0 0;
        transition: all 0.2s ease;
        min-height: 2px;
    }

    .bar-item:hover .bar {
        opacity: 0.8;
        filter: brightness(0.9);
    }

    .bar-label {
        font-size: 0.75rem;
        text-align: center;
        color: #6b7280;
        width: 100%;
    }

    .usage-table-container {
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

    .topics-list {
        display: grid;
        gap: 0.75rem;
    }

    .topic-item {
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
    }

    .topic-item strong {
        color: #1f2937;
    }

    .topic-item small {
        display: block;
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .filter-form {
            grid-template-columns: 1fr;
        }

        .bar-label {
            font-size: 0.65rem;
        }
    }
</style>
@endsection
