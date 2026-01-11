@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
    </div>

    @if ($projects->count())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach ($projects as $project)
                <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $project->name }}</h3>
                    <p style="color: #666; margin-bottom: 0.5rem; font-size: 0.9rem;">
                        <strong>Key:</strong> <code style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ substr($project->project_key, 0, 16) }}...</code>
                    </p>
                    <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">
                        <strong>Devices:</strong> {{ $project->devices()->count() }}
                    </p>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('projects.show', $project) }}" class="btn" style="flex: 1; background: #667eea; color: white; padding: 0.5rem; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">View</a>
                        <a href="{{ route('projects.edit', $project) }}" class="btn" style="flex: 1; background: #f59e0b; color: white; padding: 0.5rem; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Edit</a>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" style="flex: 1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="width: 100%; background: #ef4444; color: white; padding: 0.5rem; border: none; border-radius: 6px; font-size: 0.9rem; cursor: pointer;" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #f3f4f6; border-radius: 12px; padding: 2rem; text-align: center;">
            <p style="color: #666; margin-bottom: 1rem;">No projects yet. Create your first project to get started.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
        </div>
    @endif
</div>
@endsection
