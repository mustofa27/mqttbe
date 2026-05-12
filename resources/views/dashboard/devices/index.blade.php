@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Devices</h1>
        <a href="{{ route('devices.create') }}" class="btn btn-primary">+ New Device</a>
    </div>

    @if ($devices->count())
        <div class="table-container">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Type</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $device)
                        <tr>
                            <td>{{ $device->device_id }}</td>
                            <td>{{ $device->type }}</td>
                            <td>{{ $device->project->name ?? 'N/A' }}</td>
                            <td>
                                <span class="{{ $device->active ? 'status-active' : 'status-inactive' }}">
                                    {{ $device->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('devices.show', $device) }}" class="btn-sm btn-sm-view">View</a>
                                    @if ($device->device_id !== 'sys_device')
                                        <a href="{{ route('devices.edit', $device) }}" class="btn-sm btn-sm-edit">Edit</a>
                                        <form method="POST" action="{{ route('devices.destroy', $device) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-sm btn-sm-delete" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <p>No devices yet. Create your first device to get started.</p>
            <a href="{{ route('devices.create') }}" class="btn btn-primary">Create Device</a>
        </div>
    @endif
</div>
@endsection
