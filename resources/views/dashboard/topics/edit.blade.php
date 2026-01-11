@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">Edit Topic</h1>

    <form method="POST" action="{{ route('topics.update', $topic) }}" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project</label>
            <input type="text" value="{{ $topic->project->name ?? 'N/A' }}" disabled style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; background: #f3f4f6; color: #666;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Topic Code</label>
            <input type="text" name="code" value="{{ old('code', $topic->code) }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            @error('code')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
            <p style="color: #666; font-size: 0.85rem; margin-top: 0.25rem;">Template: {project}/{device_id}/{{ $topic->code }}</p>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="enabled" value="1" {{ old('enabled', $topic->enabled) ? 'checked' : '' }} style="margin-right: 0.5rem;">
                Enabled
            </label>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">Update Topic</button>
            <a href="{{ route('topics.show', $topic) }}" class="btn" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #333; text-decoration: none; text-align: center; border-radius: 6px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
