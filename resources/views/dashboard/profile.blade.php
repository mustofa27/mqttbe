@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Your Profile</h1>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('updateProfile') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name', $user->name) }}"
                required
            >
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email', $user->email) }}"
                required
            >
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label style="color: #666; font-size: 0.9rem;">Member Since</label>
            <p style="padding: 0.75rem; background: #f8f9fa; border-radius: 4px; margin: 0;">
                {{ $user->created_at->format('F d, Y') }}
            </p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </form>
</div>

<div class="card" style="max-width: 600px; margin-top: 2rem; background: #fff5f5; border-left: 4px solid #dc3545;">
    <h3 style="color: #dc3545; margin-bottom: 1rem;">Danger Zone</h3>
    <p style="margin-bottom: 1rem; color: #666;">Once you delete your account, there is no going back. Please be certain.</p>
    <form method="POST" action="{{ route('deleteAccount') }}" onsubmit="return confirm('Are you absolutely sure? This action cannot be undone.');">
        @csrf
        @method('DELETE')
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="current_password">Confirm Password</label>
            <div class="password-toggle-wrapper">
                <input type="password" id="current_password" name="password" required>
                <button type="button" class="password-toggle-btn" data-toggle="current_password" onclick="togglePasswordVisibility('current_password')">👁️</button>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-danger" style="background-color: #dc3545; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Delete Account</button>
    </form>
</div>
@endsection
