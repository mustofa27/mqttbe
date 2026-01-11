@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">Permissions</h1>
        <a href="{{ route('permissions.create') }}" class="btn btn-primary">+ New Permission</a>
    </div>

    @if ($permissions->count())
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Project</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Device Type</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Topic Code</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Access</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem; font-weight: 500;">{{ $permission->project->name }}</td>
                            <td style="padding: 1rem;">{{ $permission->device_type }}</td>
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.9rem;">{{ $permission->topic_code }}</td>
                            <td style="padding: 1rem;">
                                <span style="background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ ucfirst($permission->access) }}</span>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('permissions.show', $permission) }}" class="btn" style="background: #667eea; color: white; padding: 0.4rem 0.8rem; text-decoration: none; border-radius: 4px; font-size: 0.85rem;">View</a>
                                    <a href="{{ route('permissions.edit', $permission) }}" class="btn" style="background: #f59e0b; color: white; padding: 0.4rem 0.8rem; text-decoration: none; border-radius: 4px; font-size: 0.85rem;">Edit</a>
                                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; border: none; border-radius: 4px; font-size: 0.85rem; cursor: pointer;" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="background: #f3f4f6; border-radius: 12px; padding: 2rem; text-align: center;">
            <p style="color: #666; margin-bottom: 1rem;">No permissions yet. Create your first permission to get started.</p>
            <a href="{{ route('permissions.create') }}" class="btn btn-primary">Create Permission</a>
        </div>
    @endif
</div>
@endsection
