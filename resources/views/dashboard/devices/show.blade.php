@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">{{ $device->device_id }}</h1>
        <a href="{{ route('devices.index') }}" class="btn" style="background: #f3f4f6; color: #333; padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px;">Back</a>
    </div>

    <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem;">Device Details</h3>
                <p style="color: #666; margin-bottom: 0.5rem;">
                    <strong>Device ID:</strong> {{ $device->device_id }}
                </p>
                <p style="color: #666; margin-bottom: 0.5rem;">
                    <strong>Type:</strong> {{ $device->type }}
                </p>
                <p style="color: #666; margin-bottom: 0.5rem;">
                    <strong>Project:</strong> {{ $device->project->name }}
                </p>
                <p style="color: #666; margin-bottom: 1rem;">
                    <strong>Status:</strong> <span style="background: {{ $device->active ? '#10b981' : '#ef4444' }}; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ $device->active ? 'Active' : 'Inactive' }}</span>
                </p>
                <a href="{{ route('devices.edit', $device) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit Device</a>
            </div>

            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem;">Created & Updated</h3>
                <p style="color: #666; margin-bottom: 0.5rem;">
                    <strong>Created:</strong> {{ $device->created_at->format('M d, Y H:i') }}
                </p>
                <p style="color: #666; margin-bottom: 1rem;">
                    <strong>Last Updated:</strong> {{ $device->updated_at->format('M d, Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
