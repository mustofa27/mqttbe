@extends('layouts.app')

@section('title', 'Setup Guide')

@section('content')
<div class="container">
    <h1 class="page-title">Setup Guide</h1>
    <p class="page-subtitle">Follow these steps to connect devices and start receiving MQTT messages.</p>

    <div class="guide-card">
        <h2>1. Create a Project</h2>
        <p>Create a project to get your project key and organize devices, topics, and permissions.</p>
        <a href="{{ route('projects.create') }}" class="btn-small">Create Project</a>
    </div>

    <div class="guide-card">
        <h2>2. Add Device</h2>
        <p>Register each sensor/device so ACL checks can allow publish and subscribe operations.</p>
        <a href="{{ route('devices.create') }}" class="btn-small">Add Device</a>
    </div>

    <div class="guide-card">
        <h2>3. Configure Topics</h2>
        <p>Create topic templates (for example: <strong>{project}/{device_id}/water_level</strong>) and enable them.</p>
        <a href="{{ route('topics.create') }}" class="btn-small">Create Topic</a>
    </div>

    <div class="guide-card">
        <h2>4. Set Permissions</h2>
        <p>Grant publish/subscribe permission for your device and topic pattern.</p>
        <a href="{{ route('permissions.create') }}" class="btn-small">Set Permissions</a>
    </div>

    <div class="guide-card">
        <h2>5. Test Publish</h2>
        <p>Publish a test payload from your device client. Check Usage and Advanced Analytics pages for live updates.</p>
        <a href="{{ route('usage.dashboard') }}" class="btn-small">Open Usage</a>
    </div>
</div>

<style>
    .page-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    .guide-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #2563eb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .guide-card h2 {
        margin: 0 0 0.5rem;
        font-size: 1.1rem;
        color: #1e3a8a;
    }

    .guide-card p {
        margin: 0 0 0.9rem;
        color: #334155;
    }

    .btn-small {
        display: inline-block;
        padding: 0.45rem 0.8rem;
        background: #4f46e5;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .btn-small:hover {
        background: #4338ca;
    }
</style>
@endsection
