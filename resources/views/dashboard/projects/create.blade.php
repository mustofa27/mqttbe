@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">Create New Project</h1>

    <form method="POST" action="{{ route('projects.store') }}" style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            @error('name')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project Key</label>
            <input type="text" name="project_key" value="{{ old('project_key') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            @error('project_key')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Project Secret</label>
            <div class="password-toggle-wrapper">
                <input type="password" id="project_secret" name="project_secret" value="{{ old('project_secret') }}" required style="width: 100%; padding: 0.75rem; padding-right: 2.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                <button type="button" class="password-toggle-btn" data-toggle="project_secret" onclick="togglePasswordVisibility('project_secret')">👁️</button>
            </div>
            @error('project_secret')
                <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">Create Project</button>
            <a href="{{ route('projects.index') }}" class="btn" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #333; text-decoration: none; text-align: center; border-radius: 6px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
