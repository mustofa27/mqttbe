@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">{{ $topic->code }}</h1>
        <a href="{{ route('topics.index') }}" class="btn" style="background: #f3f4f6; color: #333; padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px;">Back</a>
    </div>

    <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem;">Topic Details</h3>
                <p style="color: #666; margin-bottom: 0.5rem;">
                    <strong>Code:</strong> {{ $topic->code }}
                </p>
                <p style="color: #666; margin-bottom: 1rem;">
                    <strong>Status:</strong> <span style="background: {{ $topic->enabled ? '#10b981' : '#ef4444' }}; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ $topic->enabled ? 'Enabled' : 'Disabled' }}</span>
                </p>
                <a href="{{ route('topics.edit', $topic) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit Topic</a>
            </div>

            <div>
                <h3 style="font-weight: 600; margin-bottom: 1rem;">Template</h3>
                <pre style="background: #f3f4f6; padding: 1rem; border-radius: 6px; overflow: auto; max-height: 150px; font-size: 0.85rem;">{{ $topic->template }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
