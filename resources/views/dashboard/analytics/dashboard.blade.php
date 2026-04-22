@extends('layouts.app')

@section('content')
<div class="analytics-container">
    <div class="page-header">
        <h1>📊 Analytics</h1>
        <div class="header-controls">
            <a id="messageHistoryBtn" href="{{ route('messages.history') }}" class="btn-message-history">📨 Message History</a>
            <select id="projectSelect" class="form-select" onchange="loadProjectAnalytics()">
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <input type="date" id="fromDate" class="form-input" onchange="loadProjectAnalytics()">
            <input type="date" id="toDate" class="form-input" onchange="loadProjectAnalytics()">
        </div>
    </div>

    @if(auth()->user()->hasActiveSubscription() && auth()->user()->hasFeature('analytics_enabled'))
    <div class="listener-panel" id="listenerPanel">
        <div class="listener-info">
            <h3>MQTT Listener Service</h3>
            <p class="listener-subtitle">Use the selected project. Listener credentials are auto-loaded from project key/secret and device <strong>system_listener</strong>.</p>
            <div class="listener-status-row">
                <span id="listenerStateBadge" class="listener-state listener-state-unknown">UNKNOWN</span>
                <span id="listenerRawStatus" class="listener-raw-status">Checking status...</span>
            </div>
        </div>
        <div class="listener-actions">
            <button id="listenerStartBtn" class="btn-start-listener" type="button" onclick="startListenerService()">Start Listener</button>
            <button id="listenerStopBtn" class="btn-stop-listener" type="button" onclick="stopListenerService()">Stop Listener</button>
            <button id="listenerRestartBtn" class="btn-restart-listener" type="button" onclick="restartListenerService()">Restart</button>
            <button class="btn-refresh-listener" type="button" onclick="loadListenerStatus()">Refresh</button>
        </div>
    </div>
    @endif

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

        .btn-message-history {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 0.9rem;
            border-radius: 6px;
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            color: #1f2937;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .btn-message-history:hover {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #3730a3;
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

        .listener-panel {
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .listener-info h3 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
        }

        .listener-subtitle {
            margin: 0 0 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .listener-status-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .listener-state {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .listener-state-running {
            background: #d1e7dd;
            color: #0f5132;
        }

        .listener-state-stopped {
            background: #f8d7da;
            color: #842029;
        }

        .listener-state-unknown {
            background: #fff3cd;
            color: #664d03;
        }

        .listener-raw-status {
            color: #6c757d;
            font-size: 0.85rem;
            word-break: break-all;
        }

        .listener-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-start-listener,
        .btn-stop-listener,
        .btn-restart-listener,
        .btn-refresh-listener {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 0.55rem 0.85rem;
            cursor: pointer;
            background: #fff;
            font-weight: 600;
        }

        .btn-start-listener {
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .btn-stop-listener {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-restart-listener {
            border-color: #fd7e14;
            color: #fd7e14;
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

            .listener-panel {
                flex-direction: column;
                align-items: stretch;
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
        const projectDataUrlTemplate = "{{ route('analytics.project-data', ['project' => '__PROJECT__']) }}";
        const messageHistoryBaseUrl = "{{ route('messages.history') }}";
        const listenerStatusUrl = "{{ route('mqtt-listener.status') }}";
        const listenerStartUrl = "{{ route('mqtt-listener.start') }}";
        const listenerStopUrl = "{{ route('mqtt-listener.stop') }}";
        const listenerRestartUrl = "{{ route('mqtt-listener.restart') }}";

        function updateMessageHistoryLink() {
            const projectId = document.getElementById('projectSelect')?.value;
            const historyBtn = document.getElementById('messageHistoryBtn');

            if (!historyBtn || !projectId) {
                return;
            }

            const url = new URL(messageHistoryBaseUrl, window.location.origin);
            url.searchParams.set('project_id', projectId);
            historyBtn.href = `${url.pathname}${url.search}`;
        }

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

            updateMessageHistoryLink();
            loadListenerStatus();

            const url = projectDataUrlTemplate.replace('__PROJECT__', projectId) + `?from=${from}&to=${to}`;

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

        function updateListenerStatusUI(service) {
            const badge = document.getElementById('listenerStateBadge');
            const raw = document.getElementById('listenerRawStatus');
            const startBtn = document.getElementById('listenerStartBtn');
            const stopBtn = document.getElementById('listenerStopBtn');
            const restartBtn = document.getElementById('listenerRestartBtn');
            if (!badge || !raw || !startBtn || !stopBtn || !restartBtn || !service) {
                return;
            }

            badge.classList.remove('listener-state-running', 'listener-state-stopped', 'listener-state-unknown');

            if (service.running) {
                badge.textContent = 'RUNNING';
                badge.classList.add('listener-state-running');
                startBtn.disabled = true;
                stopBtn.disabled = false;
                restartBtn.disabled = false;
                startBtn.textContent = 'Running';
            } else if (service.state === 'STOPPED' || service.state === 'FATAL' || service.state === 'EXITED') {
                badge.textContent = service.state;
                badge.classList.add('listener-state-stopped');
                startBtn.disabled = false;
                stopBtn.disabled = true;
                restartBtn.disabled = false;
                startBtn.textContent = 'Start Listener';
            } else {
                badge.textContent = service.state || 'UNKNOWN';
                badge.classList.add('listener-state-unknown');
                startBtn.disabled = false;
                stopBtn.disabled = false;
                restartBtn.disabled = false;
                startBtn.textContent = 'Start Listener';
            }

            raw.textContent = service.raw || 'No status output';
        }

        function loadListenerStatus() {
            const badge = document.getElementById('listenerStateBadge');
            const projectId = document.getElementById('projectSelect')?.value;
            if (!badge) {
                return;
            }

            const url = new URL(listenerStatusUrl, window.location.origin);
            if (projectId) {
                url.searchParams.set('project_id', projectId);
            }

            fetch(`${url.pathname}${url.search}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    throw new Error(data.message || 'Failed to load listener status');
                }
                updateListenerStatusUI(data);
            })
            .catch(err => {
                document.getElementById('listenerRawStatus').textContent = err.message;
            });
        }

        function startListenerService() {
            const startBtn = document.getElementById('listenerStartBtn');
            const projectId = document.getElementById('projectSelect')?.value;
            if (!startBtn) {
                return;
            }

            if (!projectId) {
                document.getElementById('listenerRawStatus').textContent = 'Select a project first.';
                return;
            }

            startBtn.disabled = true;
            startBtn.textContent = 'Starting...';

            runListenerAction(listenerStartUrl, 'Start Listener', startBtn, {
                project_id: projectId
            });
        }

        function stopListenerService() {
            const stopBtn = document.getElementById('listenerStopBtn');
            if (!stopBtn) {
                return;
            }

            stopBtn.disabled = true;
            stopBtn.textContent = 'Stopping...';
            runListenerAction(listenerStopUrl, 'Stop Listener', stopBtn);
        }

        function restartListenerService() {
            const restartBtn = document.getElementById('listenerRestartBtn');
            const projectId = document.getElementById('projectSelect')?.value;
            if (!restartBtn) {
                return;
            }

            if (!projectId) {
                document.getElementById('listenerRawStatus').textContent = 'Select a project first.';
                return;
            }

            restartBtn.disabled = true;
            restartBtn.textContent = 'Restarting...';
            runListenerAction(listenerRestartUrl, 'Restart', restartBtn, {
                project_id: projectId
            });
        }

        function runListenerAction(url, defaultLabel, actionButton, payload = null) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: payload ? JSON.stringify(payload) : null
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    throw new Error(data.message || 'Failed to update listener service');
                }
                updateListenerStatusUI(data.service);
                if (data.message) {
                    document.getElementById('listenerRawStatus').textContent = data.message;
                }
                actionButton.disabled = false;
                actionButton.textContent = defaultLabel;
            })
            .catch(err => {
                document.getElementById('listenerRawStatus').textContent = err.message;
                actionButton.disabled = false;
                actionButton.textContent = defaultLabel;
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initializeDatepickers();
            loadProjectAnalytics();
            loadListenerStatus();

            // Auto-refresh every 30 seconds
            setInterval(loadProjectAnalytics, 30000);
            setInterval(loadListenerStatus, 10000);
        });
    </script>
</div>
@endsection
