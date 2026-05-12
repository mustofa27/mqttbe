@extends('layouts.app')

@section('content')
<div class="mqtt-listeners-page">
    <div class="mqtt-admin-header">
        <div>
            <h1 class="admin-header-title">🛰️ MQTT Listener Monitor</h1>
            <p class="admin-header-subtitle">Admin visibility into per-user listener processes and limits</p>
        </div>
        <div class="mqtt-controls">
            <input id="filterUserName" type="text" placeholder="Filter by user name…" class="mqtt-filter-input">
            <button id="refreshBtn" type="button" class="mqtt-refresh-btn">Refresh</button>
        </div>
    </div>

    <div class="mqtt-stats">
        <div class="mqtt-stat-card">
            <div class="mqtt-stat-label">Visible Users</div>
            <div id="statUsers" class="mqtt-stat-value">0</div>
        </div>
        <div class="mqtt-stat-card">
            <div class="mqtt-stat-label">Running Projects</div>
            <div id="statRunning" class="mqtt-stat-value mqtt-stat-value-green">0</div>
        </div>
        <div class="mqtt-stat-card">
            <div class="mqtt-stat-label">Limit Reached</div>
            <div id="statLimited" class="mqtt-stat-value mqtt-stat-value-red">0</div>
        </div>
    </div>

    <div class="mqtt-table-container">
        <div class="mqtt-table-header">
            <h2>Per-Project Listener Sessions</h2>
            <span id="lastUpdated" class="mqtt-table-last-updated">Not updated yet</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="mqtt-listeners-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Tier</th>
                        <th>Project</th>
                        <th>State</th>
                        <th>PID</th>
                        <th>Running / Limit</th>
                        <th>Started</th>
                        <th>Log Path</th>
                    </tr>
                </thead>
                <tbody id="listenersTableBody">
                    <tr class="mqtt-table-body-row">
                        <td colspan="8" style="text-align: center; color: #6b7280;">Loading listener data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const endpoint = "{{ route('admin.mqtt-listeners.index') }}";
    const tbody = document.getElementById('listenersTableBody');
    const statUsers = document.getElementById('statUsers');
    const statRunning = document.getElementById('statRunning');
    const statLimited = document.getElementById('statLimited');
    const lastUpdated = document.getElementById('lastUpdated');
    const filterInput = document.getElementById('filterUserName');
    const refreshBtn = document.getElementById('refreshBtn');
    let allRows = [];


    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatStateBadge(state) {
        const normalized = (state || 'UNKNOWN').toUpperCase();
        let badgeClass = 'mqtt-state-unknown';
        if (normalized === 'RUNNING') badgeClass = 'mqtt-state-running';
        if (normalized === 'STOPPED') badgeClass = 'mqtt-state-stopped';
        return `<span class="mqtt-state-badge ${badgeClass}">${escapeHtml(normalized)}</span>`;
    }

    function updateStats(rows) {
        let runningProjects = 0;
        let limitedUsers = 0;
        rows.forEach(r => {
            if (Array.isArray(r.projects)) {
                runningProjects += r.projects.filter(p => p.running).length;
            }
            if (r.limit_reached) limitedUsers++;
        });
        statUsers.textContent = rows.length;
        statRunning.textContent = runningProjects;
        statLimited.textContent = limitedUsers;
        lastUpdated.textContent = 'Updated: ' + new Date().toLocaleString();
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr class="mqtt-table-body-row"><td colspan="8" style="text-align: center; color: #6b7280;">No users found for this filter.</td></tr>';
            return;
        }

        const htmlParts = [];

        rows.forEach((entry) => {
            const user = entry.user || {};
            const tier = user.subscription_tier || 'unknown';
            const limit = entry.limit === -1 ? '∞' : (entry.limit ?? 0);
            const countLimit = `${entry.running_count ?? 0} / ${limit}`;
            const projects = Array.isArray(entry.projects) ? entry.projects : [];
            const rowSpan = projects.length || 1;

            if (projects.length === 0) {
                htmlParts.push(`
                    <tr class="mqtt-table-body-row">
                        <td>
                            <div class="mqtt-user-info">${escapeHtml(user.name || '-')}</div>
                            <div class="mqtt-user-meta">#${escapeHtml(user.id || '-')} · ${escapeHtml(user.email || '-')}</div>
                        </td>
                        <td style="text-transform: capitalize;">${escapeHtml(tier)}</td>
                        <td colspan="4" style="color: #6b7280; font-style: italic;">No active projects</td>
                        <td>${escapeHtml(countLimit)}${entry.limit_reached ? ' <span style="color: #991b1b; font-weight: 700;">(limit)</span>' : ''}</td>
                        <td style="color: #6b7280; font-size: 0.8rem;">-</td>
                    </tr>
                `);
                return;
            }

            projects.forEach((proj, idx) => {
                const isFirst = idx === 0;
                const userCell = isFirst ? `
                    <td rowspan="${rowSpan}" style="border-right: 2px solid #e0e7ff;">
                        <div class="mqtt-user-info">${escapeHtml(user.name || '-')}</div>
                        <div class="mqtt-user-meta">#${escapeHtml(user.id || '-')} · ${escapeHtml(user.email || '-')}</div>
                    </td>
                    <td rowspan="${rowSpan}" style="text-transform: capitalize; border-right: 1px solid #e5e7eb;">${escapeHtml(tier)}</td>
                ` : '';

                const countLimitCell = isFirst ? `
                    <td rowspan="${rowSpan}" style="border-left: 1px solid #e5e7eb;">
                        ${escapeHtml(countLimit)}${entry.limit_reached ? ' <span style="color: #991b1b; font-weight: 700;">(limit)</span>' : ''}
                    </td>
                ` : '';

                const rowClass = isFirst ? 'mqtt-table-body-row' : 'mqtt-table-body-row';
                const rowStyle = isFirst ? 'background: #f8f9ff;' : '';

                htmlParts.push(`
                    <tr class="${rowClass}" style="${rowStyle}">
                        ${userCell}
                        <td>
                            <div class="mqtt-project-name">${escapeHtml(proj.project_name || '-')}</div>
                            <div class="mqtt-project-id">ID: ${escapeHtml(proj.project_id)}</div>
                        </td>
                        <td>${formatStateBadge(proj.state)}</td>
                        <td>${escapeHtml(proj.pid > 0 ? proj.pid : '-')}</td>
                        ${countLimitCell}
                        <td style="color: #6b7280; font-size: 0.85rem;">${escapeHtml(proj.started_at || '-')}</td>
                        <td style="color: #6b7280; font-size: 0.8rem;">${escapeHtml(proj.log_path || '-')}</td>
                    </tr>
                `);
            });
        });

        tbody.innerHTML = htmlParts.join('');
    }

    function applyFilter() {
        const query = filterInput.value.trim().toLowerCase();
        const filtered = query
            ? allRows.filter(r => {
                const name = (r.user?.name || '').toLowerCase();
                const email = (r.user?.email || '').toLowerCase();
                return name.includes(query) || email.includes(query);
            })
            : allRows;
        updateStats(filtered);
        renderRows(filtered);
    }

    function loadData() {
        const params = new URLSearchParams();
        params.set('json', '1');

        refreshBtn.disabled = true;
        refreshBtn.textContent = 'Refreshing...';

        fetch(endpoint + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || !data.ok) {
                throw new Error(data.message || 'Failed to load listener overview');
            }

            allRows = Array.isArray(data.data) ? data.data : [];
            applyFilter();
        })
        .catch((error) => {
            tbody.innerHTML = `<tr class="mqtt-table-body-row"><td colspan="8" style="text-align: center; color: #991b1b;">${escapeHtml(error.message)}</td></tr>`;
        })
        .finally(() => {
            refreshBtn.disabled = false;
            refreshBtn.textContent = 'Refresh';
        });
    }

    filterInput.addEventListener('input', applyFilter);
    filterInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilter();
        }
    });
    refreshBtn.addEventListener('click', loadData);

    loadData();
    setInterval(loadData, 15000);
})();
</script>
@endsection
