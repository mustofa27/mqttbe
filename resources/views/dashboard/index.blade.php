@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')


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



<div style="margin-top: 2rem; background: white; padding: 2rem; border-radius: 8px; border-left: 4px solid #007bff;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">IoT Project Setup Guide</h2>
    <p style="color: #4b5563; margin: 0;">The complete setup guide has moved to a dedicated page for easier reading and copy/paste examples.</p>
    <a href="{{ route('setup.guide') }}" style="display: inline-block; margin-top: 1rem; background: #4f46e5; color: white; text-decoration: none; padding: 0.5rem 0.85rem; border-radius: 6px; font-weight: 600;">Open Setup Guide</a>
</div>
@endsection
