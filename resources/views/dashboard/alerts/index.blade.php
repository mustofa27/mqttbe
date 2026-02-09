@extends('layouts.app')

@section('content')
<div class="alerts-container">
    <div class="page-header">
        <h1>🔔 Alerts</h1>
        <button class="btn btn-primary" onclick="document.getElementById('createAlertModal').showModal()">
            + Create Alert
        </button>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger">{{ $message }}</div>
    @endif

    <div class="alerts-list">
        @if ($alerts->isEmpty())
            <div class="empty-state">
                <p>No alerts configured yet. Create one to stay notified.</p>
                <button class="btn btn-secondary" onclick="document.getElementById('createAlertModal').showModal()">
                    Create your first alert
                </button>
            </div>
        @else
            @foreach ($alerts as $alert)
                <div class="alert-card">
                    <div class="alert-header">
                        <div>
                            <h3>{{ $alert->name }}</h3>
                            <p class="alert-type">{{ $alertTypes[$alert->type] ?? $alert->type }}</p>
                        </div>
                        <span class="alert-status {{ $alert->active ? 'active' : 'inactive' }}">
                            {{ $alert->active ? '🟢 Active' : '🔴 Inactive' }}
                        </span>
                    </div>

                    <div class="alert-recipients">
                        <span class="recipient-label">Recipients:</span>
                        <div class="recipient-list">
                            @foreach ($alert->recipients as $email)
                                <span class="recipient-badge">{{ $email }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="alert-meta">
                        <div class="meta-item">
                            <span class="meta-label">Condition:</span>
                            <span class="meta-value">{{ ucfirst(str_replace('_', ' ', $alert->condition ?? 'N/A')) }}</span>
                        </div>
                        @if ($alert->threshold)
                            <div class="meta-item">
                                <span class="meta-label">Threshold:</span>
                                <span class="meta-value">{{ $alert->threshold }}</span>
                            </div>
                        @endif
                        <div class="meta-item">
                            <span class="meta-label">Triggered:</span>
                            <span class="meta-value">
                                {{ $alert->trigger_count }} time{{ $alert->trigger_count !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Last Triggered:</span>
                            <span class="meta-value">
                                {{ $alert->last_triggered_at ? $alert->last_triggered_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                    </div>

                    <div class="alert-actions">
                        <button class="btn btn-sm btn-info" onclick="testAlert({{ $alert->id }})">
                            Test
                        </button>
                        <button class="btn btn-sm {{ $alert->active ? 'btn-warning' : 'btn-success' }}" 
                                onclick="toggleAlert({{ $alert->id }})">
                            {{ $alert->active ? 'Disable' : 'Enable' }}
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="editAlert({{ $alert->id }})">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('alerts.destroy', $alert) }}" 
                              style="display:inline;" 
                              onsubmit="return confirm('Delete this alert?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Create Alert Modal -->
    <dialog id="createAlertModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create Alert</h2>
                <button class="modal-close" onclick="document.getElementById('createAlertModal').close()">✕</button>
            </div>

            <form method="POST" action="{{ route('alerts.store') }}" class="modal-form">
                @csrf

                <input type="hidden" name="project_id" value="{{ request('project_id', 1) }}">

                <div class="form-group">
                    <label for="name">Alert Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., High Rate Limit" required class="form-control">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Alert Type</label>
                    <select id="type" name="type" required class="form-control" onchange="updateConditions()">
                        <option value="">Select an alert type...</option>
                        @foreach ($alertTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="conditionGroup" style="display:none;">
                    <label for="condition">Condition</label>
                    <select id="condition" name="condition" class="form-control">
                        <option value="">Select condition...</option>
                    </select>
                    @error('condition')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="thresholdGroup" style="display:none;">
                    <label for="threshold">Threshold</label>
                    <input type="number" id="threshold" name="threshold" class="form-control">
                    @error('threshold')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="recipients">Email Recipients</label>
                    <input type="email" id="recipients" placeholder="Add email and press Enter" class="form-control">
                    <div id="recipientsList" style="margin-top: 0.5rem;"></div>
                    <input type="hidden" id="recipientsInput" name="recipients" value="[]">
                    @error('recipients')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createAlertModal').close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Create Alert
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <style>
        .alerts-container {
            padding: 2rem;
            max-width: 1000px;
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

        .alerts-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .alert-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .alert-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }

        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .alert-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .alert-type {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .alert-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-status.active {
            background: #d4edda;
            color: #155724;
        }

        .alert-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-recipients {
            margin-bottom: 1rem;
        }

        .recipient-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .recipient-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .recipient-badge {
            background: #e7f3ff;
            color: #0066cc;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-family: 'Courier New', monospace;
        }

        .alert-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .meta-value {
            font-size: 0.95rem;
            color: #2c3e50;
            margin-top: 0.25rem;
        }

        .alert-actions {
            display: flex;
            gap: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-primary:hover {
            background: #0b5ed7;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5c636a;
        }

        .btn-info {
            background: #0dcaf0;
            color: white;
        }

        .btn-info:hover {
            background: #0aa4d8;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .btn-success:hover {
            background: #157347;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #ffbb00;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .modal {
            border: none;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .modal::backdrop {
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            padding: 0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6c757d;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #2c3e50;
        }

        .modal-form {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .error-message {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
    </style>

    <script>
        const conditionsMap = {
            'rate_limit_warning': ['exceeds_80_percent', 'exceeds_90_percent'],
            'quota_warning': ['exceeds_80_percent', 'exceeds_90_percent'],
            'high_message_volume': ['exceeds', 'drops_below'],
            'subscription_expiring': ['days_remaining'],
        };

        function updateConditions() {
            const type = document.getElementById('type').value;
            const conditions = conditionsMap[type] || [];
            const select = document.getElementById('condition');
            
            select.innerHTML = '<option value="">Select condition...</option>';
            conditions.forEach(cond => {
                const option = document.createElement('option');
                option.value = cond;
                option.textContent = ucfirst(cond.replace(/_/g, ' '));
                select.appendChild(option);
            });

            document.getElementById('conditionGroup').style.display = conditions.length > 0 ? 'block' : 'none';
            document.getElementById('thresholdGroup').style.display = type === 'high_message_volume' ? 'block' : 'none';
        }

        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        document.getElementById('recipients').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addRecipient(this.value);
                this.value = '';
            }
        });

        function addRecipient(email) {
            if (!email || !email.includes('@')) return;

            const list = JSON.parse(document.getElementById('recipientsInput').value || '[]');
            if (!list.includes(email)) {
                list.push(email);
                document.getElementById('recipientsInput').value = JSON.stringify(list);
                updateRecipientsList();
            }
        }

        function removeRecipient(email) {
            const list = JSON.parse(document.getElementById('recipientsInput').value || '[]');
            const filtered = list.filter(e => e !== email);
            document.getElementById('recipientsInput').value = JSON.stringify(filtered);
            updateRecipientsList();
        }

        function updateRecipientsList() {
            const list = JSON.parse(document.getElementById('recipientsInput').value || '[]');
            const html = list.map(email => `
                <div class="recipient-badge">
                    ${email}
                    <button type="button" onclick="removeRecipient('${email}')" style="margin-left:0.5rem;cursor:pointer;border:none;background:none;color:red;">✕</button>
                </div>
            `).join('');
            document.getElementById('recipientsList').innerHTML = html;
        }

        function testAlert(alertId) {
            fetch(`/alerts/${alertId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(res => res.json())
            .then(data => alert(data.message))
            .catch(err => alert('Error: ' + err.message));
        }

        function toggleAlert(alertId) {
            fetch(`/alerts/${alertId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(res => res.json())
            .then(data => location.reload())
            .catch(err => alert('Error: ' + err.message));
        }

        function editAlert(alertId) {
            alert('Edit functionality coming soon');
        }
    </script>
</div>
@endsection
