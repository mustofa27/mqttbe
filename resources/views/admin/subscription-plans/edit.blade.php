@extends('layouts.app')

@section('content')
<div class="admin-form-container" style="max-width: 700px;">
    <div class="admin-form-header">
        <h1>✏️ Edit {{ ucfirst($plan) }} Plan</h1>
        <p>Customize subscription limits and features</p>
    </div>

    <div class="admin-form-card">
        <form action="{{ route('admin.subscription-plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Resource Limits Section -->
            <div class="admin-form-section">
                <h3>📊 Resource Limits</h3>
                <div class="admin-form-grid">
                    <div class="admin-form-group">
                        <label for="price" class="admin-form-label">Price (IDR)</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $planDetails['price']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Set 0 for free plan</p>
                    </div>
                    <div class="admin-form-group">
                        <label for="max_projects" class="admin-form-label">Max Projects</label>
                        <input type="number" id="max_projects" name="max_projects" value="{{ old('max_projects', $planDetails['max_projects']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_devices_per_project" class="admin-form-label">Max Devices/Project</label>
                        <input type="number" id="max_devices_per_project" name="max_devices_per_project" value="{{ old('max_devices_per_project', $planDetails['max_devices_per_project']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_topics_per_project" class="admin-form-label">Max Topics/Project</label>
                        <input type="number" id="max_topics_per_project" name="max_topics_per_project" value="{{ old('max_topics_per_project', $planDetails['max_topics_per_project']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="rate_limit_per_hour" class="admin-form-label">Rate Limit/Hour</label>
                        <input type="number" id="rate_limit_per_hour" name="rate_limit_per_hour" value="{{ old('rate_limit_per_hour', $planDetails['rate_limit_per_hour']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_monthly_messages" class="admin-form-label">Max Monthly Messages</label>
                        <input type="number" id="max_monthly_messages" name="max_monthly_messages" value="{{ old('max_monthly_messages', $planDetails['max_monthly_messages'] ?? 0) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_api_keys" class="admin-form-label">Max API Keys</label>
                        <input type="number" id="max_api_keys" name="max_api_keys" value="{{ old('max_api_keys', $planDetails['max_api_keys'] ?? 0) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="api_rpm" class="admin-form-label">API RPM</label>
                        <input type="number" id="api_rpm" name="api_rpm" value="{{ old('api_rpm', $planDetails['api_rpm'] ?? 0) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_webhooks_per_project" class="admin-form-label">Max Webhooks/Project</label>
                        <input type="number" id="max_webhooks_per_project" name="max_webhooks_per_project" value="{{ old('max_webhooks_per_project', $planDetails['max_webhooks_per_project'] ?? 0) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="max_advance_dashboard_widgets" class="admin-form-label">Max Advanced Widgets</label>
                        <input type="number" id="max_advance_dashboard_widgets" name="max_advance_dashboard_widgets" value="{{ old('max_advance_dashboard_widgets', $planDetails['max_advance_dashboard_widgets'] ?? 0) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>

                    <div class="admin-form-group">
                        <label for="data_retention_days" class="admin-form-label">Data Retention (days)</label>
                        <input type="number" id="data_retention_days" name="data_retention_days" value="{{ old('data_retention_days', $planDetails['data_retention_days']) }}" required class="admin-form-input">
                        <p class="admin-form-help">Use -1 for unlimited</p>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="admin-form-section">
                <h3>✨ Features</h3>
                <div class="admin-form-grid">
                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="analytics_enabled" value="1" {{ old('analytics_enabled', $planDetails['analytics_enabled']) ? 'checked' : '' }}>
                        <span>📊 Analytics Enabled</span>
                    </label>

                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="advanced_analytics_enabled" value="1" {{ old('advanced_analytics_enabled', $planDetails['advanced_analytics_enabled'] ?? false) ? 'checked' : '' }}>
                        <span>🚀 Advanced Dashboard Enabled</span>
                    </label>

                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="webhooks_enabled" value="1" {{ old('webhooks_enabled', $planDetails['webhooks_enabled']) ? 'checked' : '' }}>
                        <span>🪝 Webhooks Enabled</span>
                    </label>

                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="api_access" value="1" {{ old('api_access', $planDetails['api_access']) ? 'checked' : '' }}>
                        <span>🔌 API Access</span>
                    </label>

                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="priority_support" value="1" {{ old('priority_support', $planDetails['priority_support']) ? 'checked' : '' }}>
                        <span>⚡ Priority Support</span>
                    </label>

                    <label class="admin-form-checkbox-card">
                        <input type="checkbox" name="secure_connection" value="1" checked disabled>
                        <span>🔒 Secure Connection (SSL/TLS)</span>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="admin-form-buttons">
                <button type="submit" class="admin-btn-submit">✓ Update Plan</button>
                <a href="{{ route('admin.subscription-plans.index') }}" class="admin-btn-cancel">✕ Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
