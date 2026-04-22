@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">🛰️ MQTT Listener Monitor</h1>
            <p style="color: #9ca3af; font-size: 0.9rem;">Admin visibility into per-user listener processes and limits</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <input id="filterUserName" type="text" placeholder="Filter by user name…" style="padding: 0.65rem 0.8rem; border: 1px solid #d1d5db; border-radius: 8px; width: 200px;">
            <button id="refreshBtn" type="button" style="padding: 0.65rem 1rem; border: 0; border-radius: 8px; color: white; background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-weight: 600; cursor: pointer;">Refresh</button>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; min-width: 180px;">
            <div style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Visible Users</div>
            <div id="statUsers" style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 0.25rem;">0</div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; min-width: 180px;">
            <div style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Running Projects</div>
            <div id="statRunning" style="font-size: 1.5rem; font-weight: 700; color: #065f46; margin-top: 0.25rem;">0</div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; min-width: 180px;">
            <div style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Limit Reached</div>
            <div id="statLimited" style="font-size: 1.5rem; font-weight: 700; color: #991b1b; margin-top: 0.25rem;">0</div>
        </div>
    </div>

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1rem; color: #374151;">Per-Project Listener Sessions</h2>
            <span id="lastUpdated" style="font-size: 0.8rem; color: #6b7280;">Not updated yet</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 1100px;">
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">User</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Tier</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Project</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">State</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">PID</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Running / Limit</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Started</th>
                        <th style="padding: 0.9rem 1rem; text-align: left; font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Log Path</th>
                    </tr>
                </thead>
                <tbody id="listenersTableBody">
                    <tr>
                        <td colspan="8" style="padding: 1.25rem; text-align: center; color: #6b7280;">Loading listener data...</td>
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
        let color = 'background:#fef3c7;color:#92400e;';
        if (normalized === 'RUNNING') color = 'background:#dcfce7;color:#166534;';
        if (normalized === 'STOPPED') color = 'background:#fee2e2;color:#991b1b;';
        return `<span style="display:inline-block;padding:0.25rem 0.6rem;border-radius:999px;font-size:0.75rem;font-weight:700;${color}">${escapeHtml(normalized)}</span>`;
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
            tbody.innerHTML = '<tr><td colspan="8" style="padding: 1.25rem; text-align: center; color: #6b7280;">No users found for this filter.</td></tr>';
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
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:0.9rem 1rem; vertical-align:top;">
                            <div style="font-weight:600;color:#1f2937;">${escapeHtml(user.name || '-')}</div>
                            <div style="font-size:0.8rem;color:#6b7280;">#${escapeHtml(user.id || '-')} · ${escapeHtml(user.email || '-')}</div>
                        </td>
                        <td style="padding:0.9rem 1rem; color:#374151; text-transform:capitalize; vertical-align:top;">${escapeHtml(tier)}</td>
                        <td style="padding:0.9rem 1rem; color:#6b7280; font-style:italic;" colspan="4">No active projects</td>
                        <td style="padding:0.9rem 1rem; color:#374151; vertical-align:top;">${escapeHtml(countLimit)}${entry.limit_reached ? ' <span style="color:#991b1b;font-weight:700;">(limit)</span>' : ''}</td>
                        <td style="padding:0.9rem 1rem; color:#6b7280; font-size:0.8rem;">-</td>
                    </tr>
                `);
                return;
            }

            projects.forEach((proj, idx) => {
                const isFirst = idx === 0;
                const rowStyle = `border-bottom:1px solid ${isFirst ? '#c7d2fe' : '#e5e7eb'};${isFirst ? 'background:#f8f9ff;' : ''}`;

                const userCell = isFirst ? `
                    <td rowspan="${rowSpan}" style="padding:0.9rem 1rem; vertical-align:top; border-right:2px solid #e0e7ff;">
                        <div style="font-weight:600;color:#1f2937;">${escapeHtml(user.name || '-')}</div>
                        <div style="font-size:0.8rem;color:#6b7280;">#${escapeHtml(user.id || '-')} · ${escapeHtml(user.email || '-')}</div>
                    </td>
                    <td rowspan="${rowSpan}" style="padding:0.9rem 1rem; color:#374151; text-transform:capitalize; vertical-align:top; border-right:1px solid #e5e7eb;">${escapeHtml(tier)}</td>
                ` : '';

                const countLimitCell = isFirst ? `
                    <td rowspan="${rowSpan}" style="padding:0.9rem 1rem; color:#374151; vertical-align:top; border-left:1px solid #e5e7eb;">
                        ${escapeHtml(countLimit)}${entry.limit_reached ? ' <span style="color:#991b1b;font-weight:700;">(limit)</span>' : ''}
                    </td>
                ` : '';

                htmlParts.push(`
                    <tr style="${rowStyle}">
                        ${userCell}
                        <td style="padding:0.9rem 1rem;">
                            <div style="font-weight:600;color:#1e40af;">${escapeHtml(proj.project_name || '-')}</div>
                            <div style="font-size:0.75rem;color:#9ca3af;">ID: ${escapeHtml(proj.project_id)}</div>
                        </td>
                        <td style="padding:0.9rem 1rem;">${formatStateBadge(proj.state)}</td>
                        <td style="padding:0.9rem 1rem; color:#374151;">${escapeHtml(proj.pid > 0 ? proj.pid : '-')}</td>
                        ${countLimitCell}
                        <td style="padding:0.9rem 1rem; color:#6b7280; font-size:0.85rem;">${escapeHtml(proj.started_at || '-')}</td>
                        <td style="padding:0.9rem 1rem; color:#6b7280; font-size:0.8rem;">${escapeHtml(proj.log_path || '-')}</td>
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
            tbody.innerHTML = `<tr><td colspan="8" style="padding: 1.25rem; text-align: center; color: #991b1b;">${escapeHtml(error.message)}</td></tr>`;
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
