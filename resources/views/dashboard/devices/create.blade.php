@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">Create New Device</h1>

    <form method="POST" action="{{ route('devices.store') }}" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project</label>
            <select name="project_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="">Select a project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
            @error('project_id')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Device ID</label>
            <input type="text" name="device_id" value="{{ old('device_id') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            @error('device_id')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Device Type</label>
            <select name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="">Select a device type</option>
                <option value="sensor" {{ old('type') == 'sensor' ? 'selected' : '' }}>Sensor</option>
                <option value="actuator" {{ old('type') == 'actuator' ? 'selected' : '' }}>Actuator</option>
                <option value="dashboard" {{ old('type') == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
            </select>
            @error('type')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">Create Device</button>
            <a href="{{ route('devices.index') }}" class="btn" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #333; text-decoration: none; text-align: center; border-radius: 6px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
