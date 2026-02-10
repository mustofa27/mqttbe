@extends('layouts.app')

@section('title', 'Message History')

@section('content')
<div class="container" style="max-width: 1100px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">MQTT Message History</h1>
    <form method="GET" style="margin-bottom: 2rem; display: flex; gap: 1rem;">
        <select name="device_id" style="padding: 0.5rem; border-radius: 6px;">
            <option value="">All Devices</option>
            @foreach ($projects as $project)
                @foreach ($project->devices as $device)
                    <option value="{{ $device->id }}" @if(request('device_id') == $device->id) selected @endif>{{ $device->device_id }}</option>
                @endforeach
            @endforeach
        </select>
        <input type="text" name="topic" placeholder="Topic" value="{{ request('topic') }}" style="padding: 0.5rem; border-radius: 6px;">
        <input type="date" name="date" value="{{ request('date') }}" style="padding: 0.5rem; border-radius: 6px;">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                <tr>
                    <th style="padding: 1rem; text-align: left;">Received At</th>
                    <th style="padding: 1rem; text-align: left;">Device</th>
                    <th style="padding: 1rem; text-align: left;">Topic</th>
                    <th style="padding: 1rem; text-align: left;">Payload</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 1rem;">{{ $message->received_at ?? $message->created_at }}</td>
                        <td style="padding: 1rem;">{{ $message->device->device_id ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">{{ $message->topic }}</td>
                        <td style="padding: 1rem; word-break: break-all;">{{ $message->payload }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding: 2rem; text-align: center; color: #888;">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 2rem;">
        {{ $messages->links() }}
    </div>
</div>
@endsection
