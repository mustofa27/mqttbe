@extends('layouts.app')

@section('content')
<div class="webhooks-container">
    <div class="page-header">
        <h1>🪝 Webhooks</h1>
        <button class="btn btn-primary" onclick="document.getElementById('createWebhookModal').showModal()">
            + Add Webhook
        </button>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger">{{ $message }}</div>
    @endif

    <div class="webhooks-list">
        @if ($webhooks->isEmpty())
            <div class="empty-state">
                <p>No webhooks configured yet.</p>
                <button class="btn btn-secondary" onclick="document.getElementById('createWebhookModal').showModal()">
                    Create your first webhook
                </button>
            </div>
        @else
            @foreach ($webhooks as $webhook)
                <div class="webhook-card">
                    <div class="webhook-header">
                        <div>
                            <h3>{{ $webhook->event_type }}</h3>
                            <p class="webhook-url">{{ $webhook->url }}</p>
                        </div>
                        <span class="webhook-status {{ $webhook->active ? 'active' : 'inactive' }}">
                            {{ $webhook->active ? '🟢 Active' : '🔴 Inactive' }}
                        </span>
                    </div>

                    @if ($webhook->description)
                        <p class="webhook-description">{{ $webhook->description }}</p>
                    @endif

                    <div class="webhook-meta">
                        <div class="meta-item">
                            <span class="meta-label">Last Triggered:</span>
                            <span class="meta-value">
                                {{ $webhook->last_triggered_at ? $webhook->last_triggered_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Failures:</span>
                            <span class="meta-value {{ $webhook->failure_count > 0 ? 'error' : '' }}">
                                {{ $webhook->failure_count }}
                            </span>
                        </div>
                    </div>

                    <div class="webhook-actions">
                        <button class="btn btn-sm btn-info" onclick="testWebhook({{ $webhook->id }})">
                            Test
                        </button>
                        <button class="btn btn-sm {{ $webhook->active ? 'btn-warning' : 'btn-success' }}" 
                                onclick="toggleWebhook({{ $webhook->id }})">
                            {{ $webhook->active ? 'Disable' : 'Enable' }}
                        </button>
                        <form method="POST" action="{{ route('webhooks.destroy', $webhook) }}" 
                              style="display:inline;" 
                              onsubmit="return confirm('Delete this webhook?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Create Webhook Modal -->
    <dialog id="createWebhookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Webhook</h2>
                <button class="modal-close" onclick="document.getElementById('createWebhookModal').close()">✕</button>
            </div>

            <form method="POST" action="{{ route('webhooks.store') }}" class="modal-form">
                @csrf

                <input type="hidden" name="project_id" value="{{ request('project_id', 1) }}">

                <div class="form-group">
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type" required class="form-control">
                        <option value="">Select an event...</option>
                        @foreach ($eventTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('event_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="url">Webhook URL</label>
                    <input type="url" id="url" name="url" placeholder="https://example.com/webhook" required class="form-control">
                    @error('url')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" placeholder="e.g., Send alerts to Slack" class="form-control" rows="3"></textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createWebhookModal').close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Create Webhook
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <style>
        .webhooks-container {
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

        .webhooks-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .webhook-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .webhook-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }

        .webhook-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .webhook-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .webhook-url {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }

        .webhook-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .webhook-status.active {
            background: #d4edda;
            color: #155724;
        }

        .webhook-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .webhook-description {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .webhook-meta {
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

        .meta-value.error {
            color: #dc3545;
            font-weight: 500;
        }

        .webhook-actions {
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
        function testWebhook(webhookId) {
            fetch(`/webhooks/${webhookId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            })
            .catch(err => alert('Error: ' + err.message));
        }

        function toggleWebhook(webhookId) {
            fetch(`/webhooks/${webhookId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(res => res.json())
            .then(data => {
                location.reload();
            })
            .catch(err => alert('Error: ' + err.message));
        }
    </script>
</div>
@endsection
