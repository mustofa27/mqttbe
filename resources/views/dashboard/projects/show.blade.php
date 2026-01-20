@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">{{ $project->name }}</h1>
        <a href="{{ route('projects.index') }}" class="btn" style="background: #f3f4f6; color: #333; padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px;">Back</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="font-weight: 600; margin-bottom: 1rem;">Project Details</h3>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Status:</strong> <span style="background: {{ $project->active ? '#10b981' : '#ef4444' }}; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ $project->active ? 'Active' : 'Inactive' }}</span>
            </p>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Project Key:</strong><br><code style="background: #f3f4f6; padding: 0.5rem; border-radius: 4px; font-size: 0.85rem; word-wrap: break-word; overflow-wrap: break-word; display: block; white-space: pre-wrap;">{{ $project->project_key }}</code>
            </p>
            <p style="color: #666; margin-bottom: 1rem; word-break: break-all;">
                <strong>Created:</strong> {{ $project->created_at->format('M d, Y') }}
            </p>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit Project</a>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="font-weight: 600; margin-bottom: 1rem;">Quick Stats</h3>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Devices:</strong> {{ $project->devices()->count() }}
            </p>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Topics:</strong> {{ $project->topics()->count() }}
            </p>
            <p style="color: #666; margin-bottom: 1rem;">
                <strong>Last Updated:</strong> {{ $project->updated_at->format('M d, Y') }}
            </p>
            <a href="{{ route('devices.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Manage Devices</a>
        </div>
    </div>
</div>
@endsection
