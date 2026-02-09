@extends('layouts.app')

@section('content')
<div class="api-keys-container">
    <div class="page-header">
        <h1>🔑 API Keys</h1>
        <button class="btn btn-primary" onclick="document.getElementById('createKeyModal').showModal()">
            + Create New Key
        </button>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger">
            {{ $message }}
        </div>
    @endif

    <div class="api-keys-grid">
        <div class="keys-list">
            <h2>Your API Keys</h2>
            @if ($keys->isEmpty())
                <div class="empty-state">
                    <p>You haven't created any API keys yet.</p>
                    <button class="btn btn-secondary" onclick="document.getElementById('createKeyModal').showModal()">
                        Create your first API key
                    </button>
                </div>
            @else
                <div class="keys-table">
                    @foreach ($keys as $key)
                        <div class="key-card">
                            <div class="key-card-header">
                                <div class="key-info">
                                    <h3>{{ $key->name }}</h3>
                                    <p class="key-id">ID: {{ substr($key->key, 0, 20) }}...</p>
                                </div>
                                <span class="key-status {{ $key->is_active ? 'active' : 'inactive' }}">
                                    {{ $key->is_active ? '🟢 Active' : '🔴 Inactive' }}
                                </span>
                            </div>

                            <div class="key-card-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Created:</span>
                                    <span class="meta-value">{{ $key->created_at->format('M d, Y') }}</span>
                                </div>
                                @if ($key->last_used_at)
                                    <div class="meta-item">
                                        <span class="meta-label">Last Used:</span>
                                        <span class="meta-value">{{ $key->last_used_at->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <div class="meta-item">
                                        <span class="meta-label">Last Used:</span>
                                        <span class="meta-value">Never</span>
                                    </div>
                                @endif
                                @if ($key->expires_at)
                                    <div class="meta-item">
                                        <span class="meta-label">Expires:</span>
                                        <span class="meta-value {{ $key->expires_at->isPast() ? 'expired' : '' }}">
                                            {{ $key->expires_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="key-card-actions">
                                @if ($key->is_active)
                                    <form method="POST" action="{{ route('api-keys.deactivate', $key) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('api-keys.activate', $key) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('api-keys.destroy', $key) }}" style="display:inline;" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="api-documentation">
            <h2>📚 API Documentation</h2>

            <div class="doc-section">
                <h3>Authentication</h3>
                <p>All API requests must include your API key in the Authorization header:</p>
                <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
            </div>

            <div class="doc-section">
                <h3>Base URL</h3>
                <pre><code>{{ url('/api/v1') }}</code></pre>
            </div>

            <div class="doc-section">
                <h3>Rate Limits</h3>
                <p>Rate limits depend on your subscription plan:</p>
                <table class="rate-limits-table">
                    <tr>
                        <th>Plan</th>
                        <th>Messages/Hour</th>
                        <th>Storage</th>
                    </tr>
                    <tr>
                        <td>Free</td>
                        <td>100</td>
                        <td>30 days</td>
                    </tr>
                    <tr>
                        <td>Starter</td>
                        <td>1,000</td>
                        <td>90 days</td>
                    </tr>
                    <tr>
                        <td>Professional</td>
                        <td>10,000</td>
                        <td>365 days</td>
                    </tr>
                    <tr>
                        <td>Enterprise</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                </table>
            </div>

            <div class="doc-section">
                <h3>Endpoints</h3>
                
                <h4>Projects</h4>
                <pre><code>GET    /api/v1/projects
POST   /api/v1/projects
GET    /api/v1/projects/{id}
PUT    /api/v1/projects/{id}
DELETE /api/v1/projects/{id}</code></pre>

                <h4>Devices</h4>
                <pre><code>GET    /api/v1/devices
POST   /api/v1/devices
GET    /api/v1/devices/{id}
PUT    /api/v1/devices/{id}
DELETE /api/v1/devices/{id}</code></pre>

                <h4>Messages</h4>
                <pre><code>GET    /api/v1/messages
POST   /api/v1/messages (publish)
DELETE /api/v1/messages/{id}</code></pre>

                <h4>API Keys</h4>
                <pre><code>GET    /api/v1/api-keys
POST   /api/v1/api-keys
DELETE /api/v1/api-keys/{id}
PATCH  /api/v1/api-keys/{id}/deactivate</code></pre>
            </div>

            <div class="doc-section">
                <h3>Example: Publish a Message</h3>
                <pre><code>curl -X POST {{ url('/api/v1/messages') }} \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "topic": "sensors/temperature",
    "payload": "23.5",
    "qos": 1
  }'</code></pre>
            </div>

            <div class="doc-section">
                <h3>Example: Get Projects (JavaScript)</h3>
                <pre><code>const response = await fetch('{{ url('/api/v1/projects') }}', {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
});

const projects = await response.json();
console.log(projects.data);</code></pre>
            </div>

            <div class="doc-section">
                <h3>Response Format</h3>
                <p>All responses are in JSON format:</p>
                <pre><code>{
  "data": [...],
  "message": "Success message (optional)",
  "pagination": {
    "total": 50,
    "per_page": 10
  }
}</code></pre>
            </div>

            <div class="doc-section">
                <h3>Error Responses</h3>
                <pre><code>// 401 Unauthorized
{ "error": "Invalid API key" }

// 429 Too Many Requests
{ "error": "Rate limit exceeded" }

// 422 Validation Error
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}</code></pre>
            </div>
        </div>
    </div>

    <!-- Create Key Modal -->
    <dialog id="createKeyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New API Key</h2>
                <button class="modal-close" onclick="document.getElementById('createKeyModal').close()">✕</button>
            </div>

            <form method="POST" action="{{ route('api-keys.store') }}" class="modal-form">
                @csrf

                <div class="form-group">
                    <label for="name">Key Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Production Server" required class="form-control">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expires_at">Expiration Date (Optional)</label>
                    <input type="date" id="expires_at" name="expires_at" class="form-control">
                    @error('expires_at')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createKeyModal').close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Create API Key
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <style>
        .api-keys-container {
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

        .api-keys-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 1024px) {
            .api-keys-grid {
                grid-template-columns: 1fr;
            }
        }

        .keys-list h2,
        .api-documentation h2 {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #2c3e50;
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

        .keys-table {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .key-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .key-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }

        .key-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .key-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .key-id {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }

        .key-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .key-status.active {
            background: #d4edda;
            color: #155724;
        }

        .key-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .key-card-meta {
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

        .meta-value.expired {
            color: #dc3545;
            font-weight: 500;
        }

        .key-card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .api-documentation {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 2rem;
            max-height: 800px;
            overflow-y: auto;
        }

        .doc-section {
            margin-bottom: 2rem;
        }

        .doc-section h3 {
            font-size: 1.1rem;
            margin-top: 0;
            margin-bottom: 0.75rem;
            color: #2c3e50;
        }

        .doc-section h4 {
            font-size: 0.95rem;
            margin: 1rem 0 0.5rem 0;
            color: #495057;
        }

        .doc-section p {
            color: #6c757d;
            margin: 0 0 1rem 0;
            font-size: 0.95rem;
        }

        .doc-section pre {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1rem;
            overflow-x: auto;
            margin: 0.5rem 0 1rem 0;
        }

        .doc-section code {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #2c3e50;
        }

        .rate-limits-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin: 0.5rem 0 1rem 0;
        }

        .rate-limits-table th,
        .rate-limits-table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
        }

        .rate-limits-table th {
            background: #e9ecef;
            font-weight: 600;
            color: #2c3e50;
        }

        .rate-limits-table tr:nth-child(even) {
            background: #f8f9fa;
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
</div>
@endsection
