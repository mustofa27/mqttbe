@extends('layouts.app')

@section('title', 'Message History')

@section('content')
<div class="container message-history-container">
    <h1 class="message-history-title">MQTT Message History</h1>

    <form method="GET" class="message-history-filters">
        <select name="device_id" class="filter-input">
            <option value="">All Devices</option>
            @foreach ($projects as $project)
                @foreach ($project->devices as $device)
                    <option value="{{ $device->id }}" @if(request('device_id') == $device->id) selected @endif>{{ $device->device_id }}</option>
                @endforeach
            @endforeach
        </select>
        <input type="text" name="topic" placeholder="Topic" value="{{ request('topic') }}" class="filter-input">
        <input type="date" name="date" value="{{ request('date') }}" class="filter-input">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <div class="message-history-table-wrap">
        <table class="message-history-table">
            <thead>
                <tr>
                    <th>Received At</th>
                    <th>Device</th>
                    <th>Topic</th>
                    <th>Payload</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td data-label="Received At">{{ $message->created_at }}</td>
                        <td data-label="Device">{{ $message->device->device_id ?? 'N/A' }}</td>
                        <td data-label="Topic" class="topic-text">{{ $message->mqtt_topic }}</td>
                        <td data-label="Payload" class="payload-text">{{ $message->payload }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $messages->links() }}
    </div>
</div>

<style>
    .message-history-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem;
    }

    .message-history-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .message-history-filters {
        margin-bottom: 2rem;
        display: grid;
        grid-template-columns: minmax(200px, 1fr) minmax(180px, 1fr) auto auto;
        gap: 0.75rem;
        align-items: center;
    }

    .filter-input {
        width: 100%;
        padding: 0.6rem 0.7rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
    }

    .message-history-table-wrap {
        background: #fff;
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .message-history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .message-history-table thead {
        background: #f3f4f6;
        border-bottom: 2px solid #e5e7eb;
    }

    .message-history-table th {
        padding: 1rem;
        text-align: left;
        color: #374151;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .message-history-table td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        color: #1f2937;
        vertical-align: top;
    }

    .message-history-table tbody tr:hover {
        background: #f9fafb;
    }

    .topic-text,
    .payload-text {
        word-break: break-all;
    }

    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #888;
    }

    .pagination-wrap {
        margin-top: 1.5rem;
    }

    .pagination-wrap svg {
        width: 0.9rem;
        height: 0.9rem;
    }

    .pagination-wrap nav > div > div > span,
    .pagination-wrap nav > div > div > a,
    .pagination-wrap nav > div > span,
    .pagination-wrap nav > div > a {
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
        line-height: 1.1;
    }

    @media (max-width: 768px) {
        .message-history-container {
            padding: 1rem;
        }

        .message-history-title {
            font-size: 1.5rem;
        }

        .message-history-filters {
            grid-template-columns: 1fr;
        }

        .message-history-table {
            min-width: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .message-history-table thead {
            display: none;
        }

        .message-history-table tbody tr {
            display: block;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.6rem 0.2rem;
        }

        .message-history-table tbody td {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 0.5rem;
            padding: 0.45rem 0.8rem;
            border-bottom: none;
        }

        .message-history-table tbody td::before {
            content: attr(data-label);
            font-size: 0.8rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .empty-state {
            display: block;
            text-align: center;
        }
    }
</style>
@endsection
