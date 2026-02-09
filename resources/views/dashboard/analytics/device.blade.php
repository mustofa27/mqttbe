@extends('layouts.app')

@section('content')
<div class="device-analytics-container">
    <div class="page-header">
        <div>
            <h1>📱 Device Analytics: {{ $device->device_id }}</h1>
            <p class="device-info">Type: {{ ucfirst($device->type) }} | Project: {{ $project->name }}</p>
        </div>
        <div class="header-controls">
            <input type="date" id="fromDate" class="form-input" onchange="loadDeviceAnalytics()">
            <input type="date" id="toDate" class="form-input" onchange="loadDeviceAnalytics()">
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="summary-stats" id="summaryStats">
        <div class="stat-card loading">
            <div class="skeleton"></div>
        </div>
        <div class="stat-card loading">
            <div class="skeleton"></div>
        </div>
        <div class="stat-card loading">
            <div class="skeleton"></div>
        </div>
        <div class="stat-card loading">
            <div class="skeleton"></div>
        </div>
        <div class="stat-card loading">
            <div class="skeleton"></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-container">
            <h3>Activity Timeline</h3>
            <canvas id="activityChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Topics Used</h3>
            <canvas id="topicsChart"></canvas>
        </div>

        <div class="chart-container full-width">
            <h3>QoS Distribution</h3>
            <canvas id="qosChart"></canvas>
        </div>
    </div>
</div>

<style>
    .device-analytics-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        margin: 0;
        font-size: 2rem;
    }

    .device-info {
        margin: 0.5rem 0 0 0;
        color: #6c757d;
        font-size: 0.95rem;
    }

    .header-controls {
        display: flex;
        gap: 1rem;
    }

    .form-input {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 0.95rem;
    }

    .form-input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .stat-card h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
    }

    .stat-card .value {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-card .unit {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .stat-card.loading {
        background: #f8f9fa;
    }

    .skeleton {
        height: 60px;
        background: linear-gradient(90deg, #e9ecef 25%, #f8f9fa 50%, #e9ecef 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    .chart-container {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .chart-container.full-width {
        grid-column: 1 / -1;
    }

    .chart-container h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .chart-container canvas {
        max-height: 300px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    const projectId = "{{ $project->id }}";
    const deviceId = "{{ $device->id }}";
    let charts = {};
    const deviceDataUrlTemplate = "{{ route('analytics.device-data', ['project' => '__PROJECT__', 'device' => '__DEVICE__']) }}";

    function initializeDatepickers() {
        const today = new Date();
        const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);

        document.getElementById('fromDate').valueAsDate = sevenDaysAgo;
        document.getElementById('toDate').valueAsDate = today;
    }

    function loadDeviceAnalytics() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;

        const url = deviceDataUrlTemplate
            .replace('__PROJECT__', projectId)
            .replace('__DEVICE__', deviceId) + `?from=${from}&to=${to}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                renderStats(data.summary);
                renderActivityChart(data.activity);
                renderTopicsChart(data.topics);
                renderQosChart(data.qos_distribution);
            })
            .catch(err => console.error('Error:', err));
    }

    function renderStats(summary) {
        const html = `
            <div class="stat-card">
                <h4>Total Messages</h4>
                <div class="value">${summary.total_messages.toLocaleString()}</div>
            </div>
            <div class="stat-card">
                <h4>Topics</h4>
                <div class="value">${summary.unique_topics}</div>
            </div>
            <div class="stat-card">
                <h4>Avg Message Size</h4>
                <div class="value">${summary.avg_message_size.toLocaleString()}</div>
                <div class="unit">bytes</div>
            </div>
            <div class="stat-card">
                <h4>Retained Count</h4>
                <div class="value">${summary.retained_count.toLocaleString()}</div>
            </div>
            <div class="stat-card">
                <h4>Last Activity</h4>
                <div class="value">${summary.last_activity || 'Never'}</div>
            </div>
        `;
        document.getElementById('summaryStats').innerHTML = html;
    }

    function renderActivityChart(data) {
        const ctx = document.getElementById('activityChart').getContext('2d');
        if (charts.activity) charts.activity.destroy();

        charts.activity = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    function renderTopicsChart(data) {
        const ctx = document.getElementById('topicsChart').getContext('2d');
        if (charts.topics) charts.topics.destroy();

        charts.topics = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    function renderQosChart(data) {
        const ctx = document.getElementById('qosChart').getContext('2d');
        if (charts.qos) charts.qos.destroy();

        charts.qos = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: { responsive: true },
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initializeDatepickers();
        loadDeviceAnalytics();
    });
</script>
@endsection
