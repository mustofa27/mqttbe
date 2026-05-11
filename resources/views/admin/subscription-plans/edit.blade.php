@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0; max-width: 700px;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">✏️ Edit {{ ucfirst($plan) }} Plan</h1>
        <p style="color: #9ca3af; font-size: 0.9rem;">Customize subscription limits and features</p>
    </div>

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 2rem; border: 1px solid #e5e7eb;">
        <form action="{{ route('admin.subscription-plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #e5e7eb;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">📊 Resource Limits</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="price" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Price (IDR)</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $planDetails['price']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Set 0 for free plan</p>
                    </div>
                    <div>
                        <label for="max_projects" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Projects</label>
                        <input type="number" id="max_projects" name="max_projects" value="{{ old('max_projects', $planDetails['max_projects']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_devices_per_project" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Devices/Project</label>
                        <input type="number" id="max_devices_per_project" name="max_devices_per_project" value="{{ old('max_devices_per_project', $planDetails['max_devices_per_project']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_topics_per_project" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Topics/Project</label>
                        <input type="number" id="max_topics_per_project" name="max_topics_per_project" value="{{ old('max_topics_per_project', $planDetails['max_topics_per_project']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="rate_limit_per_hour" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Rate Limit/Hour</label>
                        <input type="number" id="rate_limit_per_hour" name="rate_limit_per_hour" value="{{ old('rate_limit_per_hour', $planDetails['rate_limit_per_hour']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_monthly_messages" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Monthly Messages</label>
                        <input type="number" id="max_monthly_messages" name="max_monthly_messages" value="{{ old('max_monthly_messages', $planDetails['max_monthly_messages'] ?? 0) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_api_keys" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max API Keys</label>
                        <input type="number" id="max_api_keys" name="max_api_keys" value="{{ old('max_api_keys', $planDetails['max_api_keys'] ?? 0) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="api_rpm" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">API RPM</label>
                        <input type="number" id="api_rpm" name="api_rpm" value="{{ old('api_rpm', $planDetails['api_rpm'] ?? 0) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_webhooks_per_project" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Webhooks/Project</label>
                        <input type="number" id="max_webhooks_per_project" name="max_webhooks_per_project" value="{{ old('max_webhooks_per_project', $planDetails['max_webhooks_per_project'] ?? 0) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="max_advance_dashboard_widgets" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Max Advanced Widgets</label>
                        <input type="number" id="max_advance_dashboard_widgets" name="max_advance_dashboard_widgets" value="{{ old('max_advance_dashboard_widgets', $planDetails['max_advance_dashboard_widgets'] ?? 0) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>

                    <div>
                        <label for="data_retention_days" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Data Retention (days)</label>
                        <input type="number" id="data_retention_days" name="data_retention_days" value="{{ old('data_retention_days', $planDetails['data_retention_days']) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease;" onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#d1d5db';">
                        <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 0.25rem;">Use -1 for unlimited</p>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">✨ Features</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="analytics_enabled" value="1" {{ old('analytics_enabled', $planDetails['analytics_enabled']) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">📊 Analytics Enabled</span>
                    </label>

                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="advanced_analytics_enabled" value="1" {{ old('advanced_analytics_enabled', $planDetails['advanced_analytics_enabled'] ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">🚀 Advanced Dashboard Enabled</span>
                    </label>

                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="webhooks_enabled" value="1" {{ old('webhooks_enabled', $planDetails['webhooks_enabled']) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">🪝 Webhooks Enabled</span>
                    </label>

                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="api_access" value="1" {{ old('api_access', $planDetails['api_access']) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">🔌 API Access</span>
                    </label>

                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="priority_support" value="1" {{ old('priority_support', $planDetails['priority_support']) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">⚡ Priority Support</span>
                    </label>

                    <label style="display: flex; align-items: center; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='#f9fafb';">
                        <input type="checkbox" name="secure_connection" value="1" checked disabled style="width: 18px; height: 18px; cursor: pointer; margin-right: 0.75rem;">
                        <span style="color: #374151; font-weight: 500;">🔒 Secure Connection (SSL/TLS)</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" style="flex: 1; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 1rem;">
                    ✓ Update Plan
                </button>
                <a href="{{ route('admin.subscription-plans.index') }}" style="flex: 1; padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 1rem; text-align: center; text-decoration: none;">
                    ✕ Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
