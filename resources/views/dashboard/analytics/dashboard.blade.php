@extends('layouts.app')

@section('content')
<div class="analytics-container">
    <div class="page-header">
        <h1>📊 Advanced Analytics</h1>
        <div class="header-controls">
            <select id="projectSelect" class="form-select" onchange="loadProjectAnalytics()">
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <input type="date" id="fromDate" class="form-input" onchange="loadProjectAnalytics()">
            <input type="date" id="toDate" class="form-input" onchange="loadProjectAnalytics()">
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards" id="summaryCards">
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
        <div class="card loading">
            <div class="skeleton"></div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Message Volume Chart -->
        <div class="chart-container">
            <h3>Message Volume (30 Days)</h3>
            <canvas id="volumeChart"></canvas>
        </div>

        <!-- Hourly Rate Chart -->
        <div class="chart-container">
            <h3>Current Hourly Rate</h3>
            <canvas id="hourlyRateChart"></canvas>
        </div>

        <!-- Device Distribution -->
        <div class="chart-container">
            <h3>Device Distribution</h3>
            <canvas id="deviceChart"></canvas>
        </div>

        <!-- Topic Usage -->
        <div class="chart-container">
            <h3>Top 10 Topics</h3>
            <canvas id="topicChart"></canvas>
        </div>

        <!-- Growth Trend -->
        <div class="chart-container">
            <h3>Weekly Trend</h3>
            <canvas id="growthChart"></canvas>
        </div>

        <!-- Top Devices Table -->
        <div class="chart-container">
            <h3>🔥 Top 5 Active Devices</h3>
            <table class="devices-table" id="topDevicesTable">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Messages</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <style>
        .analytics-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .header-controls {
            display: flex;
            gap: 1rem;
        }

        .form-select, .form-input {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        .card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .card .unit {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .card.loading {
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

        .chart-container h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .chart-container canvas {
            max-height: 300px;
        }

        .devices-table {
            width: 100%;
            border-collapse: collapse;
        }

        .devices-table th,
        .devices-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .devices-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .devices-table tr:hover {
            background: #f8f9fa;
            cursor: pointer;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        let charts = {};

        function initializeDatepickers() {
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

            document.getElementById('fromDate').valueAsDate = thirtyDaysAgo;
            document.getElementById('toDate').valueAsDate = today;
        }

        function loadProjectAnalytics() {
            const projectId = document.getElementById('projectSelect').value;
            const from = document.getElementById('fromDate').value;
            const to = document.getElementById('toDate').value;

            fetch(`/api/analytics/project/${projectId}?from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    renderSummary(data.summary);
                    renderCharts(data);
                })
                .catch(err => console.error('Error:', err));
        }

        function renderSummary(summary) {
            const html = `
                <div class="card">
                    <h4>Total Messages</h4>
                    <div class="value">${summary.total_messages.toLocaleString()}</div>
                </div>
                <div class="card">
                    <h4>Active Devices</h4>
                    <div class="value">${summary.unique_devices}</div>
                </div>
                <div class="card">
                    <h4>Topics Used</h4>
                    <div class="value">${summary.unique_topics}</div>
                </div>
                <div class="card">
                    <h4>Avg Message Size</h4>
                    <div class="value">${summary.avg_message_size.toLocaleString()}</div>
                    <div class="unit">bytes</div>
                </div>
                <div class="card">
                    <h4>QoS ≥ 1</h4>
                    <div class="value">${summary.qos1_messages.toLocaleString()}</div>
                </div>
                <div class="card">
                    <h4>Retained Messages</h4>
                    <div class="value">${summary.retained_messages.toLocaleString()}</div>
                </div>
            `;
            document.getElementById('summaryCards').innerHTML = html;
        }

        function renderCharts(data) {
            // Volume Chart
            renderChart('volumeChart', data.volume_chart, 'line');

            // Hourly Rate Chart
            renderChart('hourlyRateChart', data.hourly_rate, 'line');

            // Device Distribution
            renderChart('deviceChart', data.device_distribution, 'doughnut');

            // Topic Usage
            renderChart('topicChart', data.topic_usage, 'bar');

            // Growth Trend
            renderChart('growthChart', data.growth_trend, 'bar');

            // Top Devices Table
            renderTopDevicesTable(data.top_devices);
        }

        function renderChart(canvasId, chartData, type) {
            const ctx = document.getElementById(canvasId).getContext('2d');

            if (charts[canvasId]) {
                charts[canvasId].destroy();
            }

            charts[canvasId] = new Chart(ctx, {
                type: type,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: type !== 'doughnut',
                        },
                    },
                    scales: type !== 'doughnut' ? {
                        y: {
                            beginAtZero: true,
                        },
                    } : undefined,
                },
            });
        }

        function renderTopDevicesTable(devices) {
            const tbody = document.querySelector('#topDevicesTable tbody');
            tbody.innerHTML = devices.map(device => `
                <tr>
                    <td>${device.device_id}</td>
                    <td><strong>${device.count.toLocaleString()}</strong></td>
                </tr>
            `).join('');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initializeDatepickers();
            loadProjectAnalytics();

            // Auto-refresh every 30 seconds
            setInterval(loadProjectAnalytics, 30000);
        });
    </script>
</div>
@endsection
