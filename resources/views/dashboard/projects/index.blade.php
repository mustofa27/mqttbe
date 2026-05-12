@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
    </div>

    @if ($projects->count())
        <div class="card-grid">
            @foreach ($projects as $project)
                <div class="resource-card">
                    <h3>{{ $project->name }}</h3>
                    <p>
                        <strong>Key:</strong> <code>{{ substr($project->project_key, 0, 16) }}...</code>
                    </p>
                    <p>
                        <strong>Devices:</strong> {{ $project->devices()->count() }}
                    </p>
                    <div class="card-actions">
                        <a href="{{ route('projects.show', $project) }}" class="btn-action btn-view">View</a>
                        <a href="{{ route('projects.edit', $project) }}" class="btn-action btn-edit">Edit</a>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" style="flex: 1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" style="width: 100%;" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <p>No projects yet. Create your first project to get started.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
        </div>
    @endif
</div>
@endsection
