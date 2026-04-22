@extends('layouts.app')

@section('title', 'Advance Dashboard')

@section('content')
<div class="advance-dashboard-container">
    <div class="advance-dashboard-header">
        <div>
            <h1>Advance Dashboard</h1>
            <p>Build your own charts per topic. Add or remove widgets anytime.</p>
        </div>
        <button type="button" class="btn-add" id="toggleAddChartBtn">+ Add Chart</button>
    </div>

    <form method="POST" action="{{ route('advance-dashboard.widgets.store') }}" id="addChartForm" class="chart-form hidden">
        @csrf
        <div class="form-grid">
            <div>
                <label>Project</label>
                <select name="project_id" id="projectSelect" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Topic</label>
                <select name="topic_id" id="topicSelect" required></select>
            </div>
            <div>
                <label>Data Type</label>
                <select name="data_type" id="dataTypeSelect" required>
                    <option value="number">Number</option>
                    <option value="text">Text</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            <div>
                <label>Visualization</label>
                <select name="visualization_mode" required>
                    <option value="time_series">Time Series</option>
                    <option value="bar">Bar Diagram</option>
                </select>
            </div>
            <div>
                <label>Title (optional)</label>
                <input type="text" name="title" maxlength="120" placeholder="Custom chart title">
            </div>
            <div id="jsonKeyWrap" class="hidden">
                <label>JSON Key</label>
                <input type="text" name="json_key" id="jsonKeyInput" placeholder="e.g. temperature">
            </div>
            <div id="jsonTypeWrap" class="hidden">
                <label>JSON Key Datatype</label>
                <select name="json_key_type" id="jsonTypeSelect">
                    <option value="number">Number</option>
                    <option value="text">Text</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">Save Chart</button>
            <button type="button" id="cancelAddChartBtn" class="btn-cancel">Cancel</button>
        </div>
    </form>

    <div class="widgets-grid" id="widgetsGrid">
        @forelse($widgets as $widget)
            <div class="widget-card" data-widget-id="{{ $widget->id }}" data-url="{{ route('advance-dashboard.widgets.data', $widget) }}">
                <div class="widget-header">
                    <div>
                        <h3>{{ $widget->title }}</h3>
                        <p>{{ $widget->project->name ?? 'Unknown Project' }} | {{ $widget->topic->code ?? 'Unknown Topic' }}</p>
                    </div>
                    <form method="POST" action="{{ route('advance-dashboard.widgets.destroy', $widget) }}" onsubmit="return confirm('Remove this chart from Advance Dashboard?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-remove">Remove</button>
                    </form>
                </div>
                <div class="widget-canvas-wrap">
                    <canvas id="widgetCanvas{{ $widget->id }}"></canvas>
                    <div class="widget-empty" id="widgetEmpty{{ $widget->id }}"></div>
                </div>
            </div>
        @empty
            <div class="empty-dashboard">
                No charts yet. Click <strong>+ Add Chart</strong> to start building your Advance Dashboard.
            </div>
        @endforelse
    </div>
</div>

<style>
    .advance-dashboard-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
    .advance-dashboard-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; }
    .advance-dashboard-header h1 { margin: 0 0 0.3rem; font-size: 2rem; }
    .advance-dashboard-header p { margin: 0; color: #6b7280; }
    .btn-add { border: 1px solid #0d6efd; color: #0d6efd; background: #fff; padding: 0.6rem 0.9rem; border-radius: 8px; cursor: pointer; font-weight: 700; }
    .chart-form { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; }
    .chart-form.hidden { display: none; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.8rem; }
    .form-grid label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem; font-weight: 700; }
    .form-grid input, .form-grid select { width: 100%; padding: 0.6rem 0.7rem; border: 1px solid #d1d5db; border-radius: 8px; }
    .form-actions { margin-top: 0.9rem; display: flex; gap: 0.6rem; }
    .btn-save { background: #0d6efd; color: #fff; border: 1px solid #0d6efd; border-radius: 8px; padding: 0.55rem 0.9rem; cursor: pointer; font-weight: 700; }
    .btn-cancel { background: #fff; color: #334155; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.9rem; cursor: pointer; font-weight: 700; }
    .hidden { display: none; }
    .widgets-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(430px, 1fr)); gap: 1rem; }
    .widget-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; }
    .widget-header { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 0.6rem; }
    .widget-header h3 { margin: 0 0 0.2rem; font-size: 1rem; }
    .widget-header p { margin: 0; color: #64748b; font-size: 0.85rem; }
    .btn-remove { border: 1px solid #dc3545; color: #dc3545; background: #fff; border-radius: 8px; padding: 0.45rem 0.7rem; cursor: pointer; font-size: 0.82rem; font-weight: 700; }
    .widget-canvas-wrap { min-height: 290px; position: relative; }
    .widget-empty { margin-top: 0.6rem; color: #64748b; font-size: 0.85rem; }
    .empty-dashboard { background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 1.5rem; color: #64748b; }

    @media (max-width: 900px) {
        .widgets-grid { grid-template-columns: 1fr; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    const projectTopics = @json($projectTopics);
    const charts = {};

    function setTopicOptions() {
        const projectSelect = document.getElementById('projectSelect');
        const topicSelect = document.getElementById('topicSelect');
        if (!projectSelect || !topicSelect) return;

        const projectId = projectSelect.value;
        const topics = projectTopics[projectId] || [];

        topicSelect.innerHTML = '';
        topics.forEach((topic) => {
            const opt = document.createElement('option');
            opt.value = topic.id;
            opt.textContent = topic.label;
            topicSelect.appendChild(opt);
        });
    }

    function toggleJsonFields() {
        const dataTypeSelect = document.getElementById('dataTypeSelect');
        const jsonKeyWrap = document.getElementById('jsonKeyWrap');
        const jsonTypeWrap = document.getElementById('jsonTypeWrap');
        const jsonKeyInput = document.getElementById('jsonKeyInput');
        const jsonTypeSelect = document.getElementById('jsonTypeSelect');

        if (!dataTypeSelect || !jsonKeyWrap || !jsonTypeWrap || !jsonKeyInput || !jsonTypeSelect) return;

        const isJson = dataTypeSelect.value === 'json';
        jsonKeyWrap.classList.toggle('hidden', !isJson);
        jsonTypeWrap.classList.toggle('hidden', !isJson);
        jsonKeyInput.required = isJson;
        jsonTypeSelect.required = isJson;
    }

    async function renderWidget(card) {
        const widgetId = card.dataset.widgetId;
        const dataUrl = card.dataset.url;
        const canvas = document.getElementById(`widgetCanvas${widgetId}`);
        const empty = document.getElementById(`widgetEmpty${widgetId}`);

        if (!canvas || !empty || !dataUrl) return;

        try {
            const res = await fetch(dataUrl, { headers: { 'Accept': 'application/json' } });
            const payload = await res.json();
            if (!res.ok) throw new Error(payload.message || 'Failed to load chart');

            if (payload.empty) {
                empty.textContent = payload.message || 'No data to display.';
                return;
            }

            if (charts[widgetId]) {
                charts[widgetId].destroy();
            }

            charts[widgetId] = new Chart(canvas.getContext('2d'), {
                type: payload.type || 'line',
                data: {
                    labels: payload.labels || [],
                    datasets: payload.datasets || [],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });

            empty.textContent = '';
        } catch (error) {
            empty.textContent = error.message || 'Unable to render chart.';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('toggleAddChartBtn');
        const cancelBtn = document.getElementById('cancelAddChartBtn');
        const addForm = document.getElementById('addChartForm');
        const projectSelect = document.getElementById('projectSelect');
        const dataTypeSelect = document.getElementById('dataTypeSelect');

        if (toggleBtn && addForm) {
            toggleBtn.addEventListener('click', () => addForm.classList.toggle('hidden'));
        }

        if (cancelBtn && addForm) {
            cancelBtn.addEventListener('click', () => addForm.classList.add('hidden'));
        }

        if (projectSelect) {
            projectSelect.addEventListener('change', setTopicOptions);
        }

        if (dataTypeSelect) {
            dataTypeSelect.addEventListener('change', toggleJsonFields);
        }

        setTopicOptions();
        toggleJsonFields();

        document.querySelectorAll('.widget-card').forEach(renderWidget);
    });
</script>
@endsection
