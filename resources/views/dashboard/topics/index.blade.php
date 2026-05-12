@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Topics</h1>
        <a href="{{ route('topics.create') }}" class="btn btn-primary">+ New Topic</a>
    </div>

    @if ($topics->count())
        <div class="table-container">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Topic Code</th>
                        <th>Template</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topics as $topic)
                        <tr>
                            <td>{{ $topic->project->name ?? 'N/A' }}</td>
                            <td>{{ $topic->code }}</td>
                            <td style="font-family: monospace; font-size: 0.9rem;">{{ substr($topic->template, 0, 50) }}{{ strlen($topic->template) > 50 ? '...' : '' }}</td>
                            <td>
                                <span class="{{ $topic->enabled ? 'status-active' : 'status-inactive' }}">
                                    {{ $topic->enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('topics.show', $topic) }}" class="btn-sm btn-sm-view">View</a>
                                    <a href="{{ route('topics.edit', $topic) }}" class="btn-sm btn-sm-edit">Edit</a>
                                    <form method="POST" action="{{ route('topics.destroy', $topic) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-sm-delete" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <p>No topics yet. Create your first topic to get started.</p>
            <a href="{{ route('topics.create') }}" class="btn btn-primary">Create Topic</a>
        </div>
    @endif
</div>
@endsection
