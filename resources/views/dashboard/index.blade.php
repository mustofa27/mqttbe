@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 0.5rem;">Welcome, {{ Auth::user()->name }}! 👋</h1>
    <p style="color: #666;">Here's your MQTT management overview</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Projects</h3>
        <div class="value">{{ $projectsCount }}</div>
    </div>
    <div class="stat-card">
        <h3>Devices</h3>
        <div class="value">{{ $devicesCount }}</div>
    </div>
</div>

<div class="card" style="text-align: center; padding: 3rem;">
    <h2>Welcome to ICMQTT</h2>
    <p style="margin: 1rem 0; color: #666;">
        Manage your MQTT projects, devices, and topics from a single dashboard.
    </p>
</div>

<div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #007bff;">
    <h3 style="margin-bottom: 0.5rem;">Quick Start</h3>
    <ul style="margin-left: 1.5rem; color: #666;">
        <li>Create a new project to manage your MQTT infrastructure</li>
        <li>Add devices to your project to start publishing/subscribing</li>
        <li>Configure topics and permissions for fine-grained access control</li>
        <li>Monitor your MQTT connections and activity in real-time</li>
    </ul>
</div>
@endsection
