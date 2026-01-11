@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">Permission</h1>
        <a href="{{ route('permissions.index') }}" class="btn" style="background: #f3f4f6; color: #333; padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px;">Back</a>
    </div>

    <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="font-weight: 600; margin-bottom: 1rem;">Permission Details</h3>
        <p style="color: #666; margin-bottom: 0.5rem;">
            <strong>Project:</strong> {{ $permission->project->name }}
        </p>
        <p style="color: #666; margin-bottom: 0.5rem;">
            <strong>Device Type:</strong> {{ $permission->device_type }}
        </p>
        <p style="color: #666; margin-bottom: 0.5rem;">
            <strong>Topic Code:</strong> <code style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $permission->topic_code }}</code>
        </p>
        <p style="color: #666; margin-bottom: 1rem;">
            <strong>Access Level:</strong> <span style="background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ ucfirst($permission->access) }}</span>
        </p>
        <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit Permission</a>
    </div>
</div>
@endsection
