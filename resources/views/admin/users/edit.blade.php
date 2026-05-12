@extends('layouts.app')

@section('content')
<div class="admin-form-container">
    <div class="admin-form-header">
        <h1>✏️ Edit User</h1>
        <p>Update user information and permissions</p>
    </div>

    <div class="admin-form-card">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="admin-form-group">
                <label for="name" class="admin-form-label">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="admin-form-input">
                @error('name')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="email" class="admin-form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="admin-form-input">
                @error('email')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="password" class="admin-form-label">
                    Password
                    <span class="admin-form-label-hint">(leave blank to keep current)</span>
                </label>
                <input type="password" id="password" name="password" class="admin-form-input">
                @error('password')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="password_confirmation" class="admin-form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label for="subscription_tier" class="admin-form-label">Subscription Tier</label>
                <select id="subscription_tier" name="subscription_tier" class="admin-form-select">
                    <option value="free" {{ old('subscription_tier', $user->subscription_tier) == 'free' ? 'selected' : '' }}>Free</option>
                    <option value="starter" {{ old('subscription_tier', $user->subscription_tier) == 'starter' ? 'selected' : '' }}>Starter</option>
                    <option value="professional" {{ old('subscription_tier', $user->subscription_tier) == 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="enterprise" {{ old('subscription_tier', $user->subscription_tier) == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="subscription_expires_at" class="admin-form-label">Subscription Expires</label>
                <input type="datetime-local" id="subscription_expires_at" name="subscription_expires_at" value="{{ old('subscription_expires_at', $user->subscription_expires_at?->format('Y-m-d\TH:i')) }}" class="admin-form-input">
            </div>

            <div class="admin-form-checkbox-group">
                <input type="checkbox" id="subscription_active" name="subscription_active" value="1" {{ old('subscription_active', $user->subscription_active) ? 'checked' : '' }} class="admin-form-checkbox">
                <label for="subscription_active" class="admin-form-checkbox-label">Subscription Active</label>
            </div>

            <div class="admin-form-checkbox-group">
                <input type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="admin-form-checkbox">
                <label for="is_admin" class="admin-form-checkbox-label">Administrator</label>
            </div>

            <div class="admin-form-buttons">
                <button type="submit" class="admin-btn-submit">✓ Update User</button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn-cancel">✕ Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
