@extends('layouts.app')

@section('content')
<div class="analytics-container">
    <div class="page-header">
        <div>
            <h1>📊 Advanced Analytics & Reports</h1>
            <p class="subtitle">Real-time insights with advanced filtering and export capabilities</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="toggleFilterPanel()">🔍 Advanced Filters</button>
            <button class="btn btn-secondary" onclick="exportData()">📥 Export Data</button>
        </div>
    </div>

    <!-- Advanced Filter Panel -->
    <div id="filterPanel" class="filter-panel" style="display: none;">
        <div class="filter-header">
            <h3>Advanced Filters</h3>
            <button onclick="toggleFilterPanel()" class="btn-close">&times;</button>
        </div>
        <div class="filter-grid">
            <div class="filter-group">
                <label>Project</label>
                <select id="projectSelect" class="form-select" onchange="loadProjectAnalytics()">
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Date Range</label>
                <div class="date-range">
                    <input type="date" id="fromDate" class="form-input" onchange="loadProjectAnalytics()">
                    <span>to</span>
                    <input type="date" id="toDate" class="form-input" onchange="loadProjectAnalytics()">
                </div>
            </div>
            <div class="filter-group">
                <label>Device</label>
                <select id="deviceSelect" class="form-select" onchange="loadProjectAnalytics()">
                    <option value="">All Devices</option>
                    <option value="loading">Loading...</option>
                </select>
            </div>
            <div class="filter-group">
                <label>QoS Level</label>
                <select id="qosSelect" class="form-select" onchange="loadProjectAnalytics()">
                    <option value="">All QoS Levels</option>
                    <option value="0">QoS 0 (At Most Once)</option>
                    <option value="1">QoS 1 (At Least Once)</option>
                    <option value="2">QoS 2 (Exactly Once)</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Time Interval</label>
                <select id="intervalSelect" class="form-select" onchange="loadProjectAnalytics()">
                    <option value="daily">Daily</option>
                    <option value="hourly">Hourly</option>
                    <option value="weekly">Weekly</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                <button class="btn btn-outline" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="stat-card">
            <div class="stat-icon">📨</div>
            <div class="stat-content">
                <div class="stat-label">Total Messages</div>
                <div class="stat-value" id="totalMessages">-</div>
                <div class="stat-change" id="messagesChange">-</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔌</div>
            <div class="stat-content">
                <div class="stat-label">Active Devices</div>
                <div class="stat-value" id="activeDevices">-</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📍</div>
            <div class="stat-content">
                <div class="stat-label">Topics</div>
                <div class="stat-value" id="totalTopics">-</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-content">
                <div class="stat-label">Avg QoS</div>
                <div class="stat-value" id="avgQos">-</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <div class="stat-label">Avg Payload Size</div>
                <div class="stat-value" id="avgPayloadSize">-</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📌</div>
            <div class="stat-content">
                <div class="stat-label">Retained Messages</div>
                <div class="stat-value" id="retainedMessages">-</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-row">
            <div class="chart-card">
                <h3>Message Volume Over Time</h3>
                <canvas id="volumeChart" height="80"></canvas>
            </div>
        </div>
        <div class="charts-row two-col">
            <div class="chart-card">
                <h3>Device Distribution</h3>
                <canvas id="deviceChart" height="120"></canvas>
            </div>
            <div class="chart-card">
                <h3>QoS Distribution</h3>
                <canvas id="qosChart" height="120"></canvas>
            </div>
        </div>
        <div class="charts-row two-col">
            <div class="chart-card">
                <h3>Top Topics</h3>
                <canvas id="topicsChart" height="120"></canvas>
            </div>
            <div class="chart-card">
                <h3>Growth Trend</h3>
                <canvas id="growthChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Device Activity Table -->
    <div class="table-section">
        <div class="table-header">
            <h3>Device Activity Report</h3>
            <button class="btn btn-sm btn-primary" onclick="exportDeviceReport()">Export</button>
        </div>
        <table class="activity-table">
            <thead>
                <tr>
                    <th onclick="sortTable('device_name')">Device Name 🔄</th>
                    <th onclick="sortTable('message_count')">Messages 🔄</th>
                    <th onclick="sortTable('last_message')">Last Activity 🔄</th>
                    <th>Avg QoS</th>
                    <th>QoS Distribution</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="deviceActivityTable">
                <tr>
                    <td colspan="6" class="text-center">Loading data...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Time Series Analytics -->
    <div class="time-series-section">
        <h3>Time Series Analysis</h3>
        <div class="time-series-controls">
            <button class="btn-filter active" onclick="loadTimeSeries('daily')">Daily</button>
            <button class="btn-filter" onclick="loadTimeSeries('hourly')">Hourly</button>
            <button class="btn-filter" onclick="loadTimeSeries('weekly')">Weekly</button>
        </div>
        <canvas id="timeSeriesChart" height="80"></canvas>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Export Data</h2>
                <button onclick="closeExportModal()" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Select what data you want to export:</p>
                <div class="export-options">
                    <label class="export-option">
                        <input type="radio" name="export_type" value="messages" checked>
                        <span>Messages</span>
                    </label>
                    <label class="export-option">
                        <input type="radio" name="export_type" value="usage">
                        <span>Usage Logs</span>
                    </label>
                    <label class="export-option">
                        <input type="radio" name="export_type" value="analytics">
                        <span>Analytics Summary</span>
                    </label>
                    <label class="export-option">
                        <input type="radio" name="export_type" value="devices">
                        <span>Device Activity</span>
                    </label>
                    <label class="export-option">
                        <input type="radio" name="export_type" value="hourly">
                        <span>Hourly Statistics</span>
                    </label>
                </div>
                <div class="export-filters">
                    <label>
                        <input type="checkbox" id="exportFilters" checked>
                        Apply current filters
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeExportModal()" class="btn btn-outline">Cancel</button>
                <button onclick="performExport()" class="btn btn-primary">Export CSV</button>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-container {
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.page-header h1 {
    font-size: 1.8rem;
    margin: 0;
}

.subtitle {
    color: #666;
    margin: 0.25rem 0 0 0;
    font-size: 0.9rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.filter-panel {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.date-range {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.date-range input {
    flex: 1;
}

.filter-actions {
    display: flex;
    gap: 1rem;
    grid-column: 1 / -1;
    justify-content: flex-end;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    display: flex;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #0d6efd;
}

.stat-icon {
    font-size: 2rem;
}

.stat-content {
    flex: 1;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
}

.stat-change {
    color: #28a745;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.charts-section {
    margin-bottom: 2rem;
}

.charts-row {
    margin-bottom: 1.5rem;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

.charts-row.two-col {
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.chart-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chart-card h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #333;
}

.table-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 1rem;
}

.table-header h3 {
    margin: 0;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    user-select: none;
    border-bottom: 2px solid #dee2e6;
}

.activity-table td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.activity-table tr:hover {
    background: #f8f9fa;
}

.qos-badges {
    display: flex;
    gap: 0.25rem;
}

.qos-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.qos-0 { background: #e7f3ff; color: #0066cc; }
.qos-1 { background: #fff4e6; color: #ff8c00; }
.qos-2 { background: #ffe7f0; color: #d63384; }

.time-series-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.time-series-controls {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.btn-filter {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-filter.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.modal-header, .modal-footer {
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    border-bottom: none;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.modal-body {
    padding: 1.5rem;
}

.export-options {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin: 1.5rem 0;
}

.export-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.export-option:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
}

.export-option input[type="radio"] {
    cursor: pointer;
}

.export-filters {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .header-actions {
        width: 100%;
    }

    .filter-grid {
        grid-template-columns: 1fr;
    }

    .quick-stats {
        grid-template-columns: 1fr;
    }

    .charts-row.two-col {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
let charts = {};

// Generate filter API URLs using Laravel route names
function getFilterUrl(endpoint, projectId) {
    const routeMap = {
        'options': "{{ url('api/v1/filter/project/') }}/" + projectId + '/options',
        'summary': "{{ url('api/v1/filter/project/') }}/" + projectId + '/summary',
        'device-activity': "{{ url('api/v1/filter/project/') }}/" + projectId + '/device-activity',
        'time-series': "{{ url('api/v1/filter/project/') }}/" + projectId + '/time-series',
        'messages': "{{ url('api/v1/filter/project/') }}/" + projectId + '/messages'
    };
    return routeMap[endpoint] || null;
}

document.addEventListener('DOMContentLoaded', function() {
    setDefaultDates();
    loadProjectAnalytics();
    loadFilterOptions();
});

function setDefaultDates() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
    
    document.getElementById('fromDate').valueAsDate = thirtyDaysAgo;
    document.getElementById('toDate').valueAsDate = today;
}

function toggleFilterPanel() {
    const panel = document.getElementById('filterPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function loadFilterOptions() {
    const projectId = document.getElementById('projectSelect').value;
    
    if (!projectId) {
        return;
    }
    
    const url = getFilterUrl('options', projectId);
    
    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': getApiToken(),
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Failed to load options');
        return r.json();
    })
    .then(data => {
        const deviceSelect = document.getElementById('deviceSelect');
        deviceSelect.innerHTML = '<option value="">All Devices</option>';
        if (data.devices && Array.isArray(data.devices)) {
            data.devices.forEach(device => {
                deviceSelect.innerHTML += `<option value="${device.id}">${device.device_id || device.id}</option>`;
            });
        }
    })
    .catch(err => console.error('Error loading filter options:', err));
}

function loadProjectAnalytics() {
    const projectId = document.getElementById('projectSelect').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const deviceId = document.getElementById('deviceSelect').value;
    const qos = document.getElementById('qosSelect').value;

    // Load summary
    loadSummary(projectId, from, to, deviceId);
    
    // Load charts
    loadCharts(projectId, from, to, deviceId);
    
    // Load device activity
    loadDeviceActivity(projectId, from, to);
    
    // Load time series
    loadTimeSeries('daily');
}

function loadSummary(projectId, from, to, deviceId) {
    const params = new URLSearchParams({
        start_date: from,
        end_date: to,
        ...(deviceId && { device_id: deviceId })
    });

    const url = getFilterUrl('summary', projectId) + '?' + params.toString();

    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': getApiToken(),
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        if (data.error) {
            console.error('API Error:', data.error);
            return;
        }
        document.getElementById('totalMessages').textContent = (data.total_messages || 0).toLocaleString();
        document.getElementById('activeDevices').textContent = data.unique_devices || 0;
        document.getElementById('totalTopics').textContent = data.unique_topics || 0;
        document.getElementById('avgQos').textContent = (data.avg_qos?.toFixed(2) || '0');
        document.getElementById('avgPayloadSize').textContent = ((data.avg_payload_size || 0) / 1024).toFixed(2) + ' KB';
        document.getElementById('retainedMessages').textContent = data.retained_messages || '0';
    })
    .catch(err => console.error('Error loading summary:', err));
}

function loadCharts(projectId, from, to, deviceId) {
    // Charts are now built from summary data
    const params = new URLSearchParams({
        start_date: from,
        end_date: to,
        ...(deviceId && { device_id: deviceId })
    });

    const url = getFilterUrl('summary', projectId) + '?' + params.toString();

    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': getApiToken(),
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        if (!data) return;
        
        // Create QoS distribution chart from summary
        const qosData = {
            labels: ['QoS 0', 'QoS 1', 'QoS 2'],
            datasets: [{
                data: [
                    data.qos_distribution?.qos_0 || 0,
                    data.qos_distribution?.qos_1 || 0,
                    data.qos_distribution?.qos_2 || 0
                ],
                backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384']
            }]
        };
        createChart('qosChart', 'doughnut', qosData);
    })
    .catch(err => console.error('Error loading charts:', err));
}

function loadDeviceActivity(projectId, from, to) {
    const params = new URLSearchParams({
        start_date: from,
        end_date: to
    });

    const url = getFilterUrl('device-activity', projectId) + '?' + params.toString();

    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': getApiToken(),
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        const tbody = document.getElementById('deviceActivityTable');
        tbody.innerHTML = '';
        
        if (data.data && Array.isArray(data.data)) {
            data.data.slice(0, 10).forEach(device => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td><strong>${device.device_id || device.device_name || 'Unknown'}</strong></td>
                    <td>${device.message_count || 0}</td>
                    <td>${device.last_message ? new Date(device.last_message).toLocaleString() : 'N/A'}</td>
                    <td>${(device.avg_qos || 0).toFixed(2)}</td>
                    <td>
                        <div class="qos-badges">
                            <span class="qos-badge qos-0">${device.qos_0_count || 0}</span>
                            <span class="qos-badge qos-1">${device.qos_1_count || 0}</span>
                            <span class="qos-badge qos-2">${device.qos_2_count || 0}</span>
                        </div>
                    </td>
                    <td>
                        <a href="/analytics/project/${projectId}/device/${device.id}" class="btn btn-sm btn-primary">View</a>
                    </td>
                `;
            });

            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No device activity found</td></tr>';
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No device activity found</td></tr>';
        }
    })
    .catch(err => {
        console.error('Error loading device activity:', err);
        document.getElementById('deviceActivityTable').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
    });
}

function loadTimeSeries(interval) {
    const projectId = document.getElementById('projectSelect').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;

    const params = new URLSearchParams({
        start_date: from,
        end_date: to,
        interval: interval
    });

    const url = getFilterUrl('time-series', projectId) + '?' + params.toString();

    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': getApiToken(),
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        if (data.data && Array.isArray(data.data)) {
            const labels = data.data.map(d => d.time || `Week ${d.week}`);
            const counts = data.data.map(d => d.count || 0);
            
            createChart('timeSeriesChart', 'line', {
                labels: labels,
                datasets: [{
                    label: 'Messages',
                    data: counts,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            });

            // Update filter buttons
            document.querySelectorAll('.btn-filter').forEach(btn => {
                btn.classList.remove('active');
            });
            if (event && event.target) {
                event.target.classList.add('active');
            }
        }
    })
    .catch(err => console.error('Error loading time series:', err));
}

function createChart(canvasId, type, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }

    charts[canvasId] = new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: type !== 'line'
                }
            },
            scales: type === 'line' || type === 'bar' ? {
                y: { beginAtZero: true }
            } : undefined
        }
    });
}

function applyFilters() {
    loadProjectAnalytics();
    toggleFilterPanel();
}

function resetFilters() {
    setDefaultDates();
    document.getElementById('deviceSelect').value = '';
    document.getElementById('qosSelect').value = '';
    document.getElementById('intervalSelect').value = 'daily';
    loadProjectAnalytics();
}

function exportData() {
    document.getElementById('exportModal').style.display = 'flex';
}

function closeExportModal() {
    document.getElementById('exportModal').style.display = 'none';
}

function performExport() {
    const type = document.querySelector('input[name="export_type"]:checked').value;
    const projectId = document.getElementById('projectSelect').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const applyFilters = document.getElementById('exportFilters').checked;

    const params = new URLSearchParams();
    if (applyFilters) {
        params.append('start_date', from);
        params.append('end_date', to);
    }

    window.location.href = `/export/project/${projectId}/${type}?${params}`;
    closeExportModal();
}

function exportDeviceReport() {
    const projectId = document.getElementById('projectSelect').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    window.location.href = `/export/project/${projectId}/devices?start_date=${from}&end_date=${to}`;
}

function sortTable(column) {
    // Implementation for sorting device activity table
    console.log('Sorting by:', column);
}

function getApiToken() {
    // For authenticated users, use CSRF token
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const panel = document.getElementById('filterPanel');
        const modal = document.getElementById('exportModal');
        if (panel.style.display !== 'none') panel.style.display = 'none';
        if (modal.style.display !== 'none') modal.style.display = 'none';
    }
});
</script>
@endsection
