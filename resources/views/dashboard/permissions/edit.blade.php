@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">Edit Permission</h1>

    <form method="POST" action="{{ route('permissions.update', $permission) }}" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project</label>
            <select name="project_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $permission->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
            @error('project_id')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Device Type</label>
            <select name="device_type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="sensor" {{ old('device_type', $permission->device_type) == 'sensor' ? 'selected' : '' }}>Sensor</option>
                <option value="actuator" {{ old('device_type', $permission->device_type) == 'actuator' ? 'selected' : '' }}>Actuator</option>
                <option value="dashboard" {{ old('device_type', $permission->device_type) == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
            </select>
            @error('device_type')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Topic Code</label>
            <input type="text" name="topic_code" value="{{ old('topic_code', $permission->topic_code) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            @error('topic_code')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Access Level</label>
            <select name="access" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="read" {{ old('access', $permission->access) == 'read' ? 'selected' : '' }}>Read Only</option>
                <option value="write" {{ old('access', $permission->access) == 'write' ? 'selected' : '' }}>Write Only</option>
                <option value="readwrite" {{ old('access', $permission->access) == 'readwrite' ? 'selected' : '' }}>Read & Write</option>
                <option value="subscribe" {{ old('access', $permission->access) == 'subscribe' ? 'selected' : '' }}>Subscribe Only</option>
                <option value="readsubscribe" {{ old('access', $permission->access) == 'readsubscribe' ? 'selected' : '' }}>Read & Subscribe</option>
                <option value="writesubscribe" {{ old('access', $permission->access) == 'writesubscribe' ? 'selected' : '' }}>Write & Subscribe</option>
                <option value="readwritesubscribe" {{ old('access', $permission->access) == 'readwritesubscribe' ? 'selected' : '' }}>Read, Write & Subscribe</option>
            </select>
            @error('access')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">Update Permission</button>
            <a href="{{ route('permissions.show', $permission) }}" class="btn" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #333; text-decoration: none; text-align: center; border-radius: 6px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
