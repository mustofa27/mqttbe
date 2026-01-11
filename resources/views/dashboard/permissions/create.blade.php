@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">Create New Permission</h1>

    <form method="POST" action="{{ route('permissions.store') }}" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project</label>
            <select id="project_id" name="project_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" onchange="filterTopics()">
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
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Device Type</label>
            <select name="device_type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="">Select a device type</option>
                <option value="sensor" {{ old('device_type') == 'sensor' ? 'selected' : '' }}>Sensor</option>
                <option value="actuator" {{ old('device_type') == 'actuator' ? 'selected' : '' }}>Actuator</option>
                <option value="dashboard" {{ old('device_type') == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
            </select>
            @error('device_type')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Topic Code</label>
            <select id="topic_code" name="topic_code" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="">Select a topic code</option>
                @foreach ($topics as $topic)
                    <option value="{{ $topic['code'] }}" data-project-id="{{ $topic['project_id'] }}" {{ old('topic_code') == $topic['code'] ? 'selected' : '' }}>{{ $topic['code'] }}</option>
                @endforeach
            </select>
            @error('topic_code')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Access Level</label>
            <select name="access" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <option value="">Select access level</option>
                <option value="read" {{ old('access') == 'read' ? 'selected' : '' }}>Read Only</option>
                <option value="write" {{ old('access') == 'write' ? 'selected' : '' }}>Write Only</option>
                <option value="readwrite" {{ old('access') == 'readwrite' ? 'selected' : '' }}>Read & Write</option>
            </select>
            @error('access')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">Create Permission</button>
            <a href="{{ route('permissions.index') }}" class="btn" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #333; text-decoration: none; text-align: center; border-radius: 6px;">Cancel</a>
        </div>
    </form>
</div>

<script>
function filterTopics() {
    const projectId = document.getElementById('project_id').value;
    const topicSelect = document.getElementById('topic_code');
    const options = topicSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
        } else {
            const optionProjectId = option.getAttribute('data-project-id');
            option.style.display = optionProjectId === projectId ? 'block' : 'none';
        }
    });
    
    // Clear selection if filtered out
    if (topicSelect.value) {
        const selected = topicSelect.options[topicSelect.selectedIndex];
        if (selected.style.display === 'none') {
            topicSelect.value = '';
        }
    }
}

// Run filter on page load
document.addEventListener('DOMContentLoaded', filterTopics);
</script>
@endsection
