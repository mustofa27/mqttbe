@extends('layouts.app')

@section('content')
<div class="admin-form-container">
    <div class="admin-form-header">
        <h1>➕ Create New User</h1>
        <p>Add a new user to the system</p>
    </div>

    <div class="admin-form-card">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="admin-form-group">
                <label for="name" class="admin-form-label">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="admin-form-input">
                @error('name')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="email" class="admin-form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="admin-form-input">
                @error('email')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="password" class="admin-form-label">Password</label>
                <input type="password" id="password" name="password" required class="admin-form-input">
                @error('password')
                    <p class="admin-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-form-group">
                <label for="password_confirmation" class="admin-form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="admin-form-input">
            </div>

            <div class="admin-form-group">
                <label for="subscription_tier" class="admin-form-label">Subscription Tier</label>
                <select id="subscription_tier" name="subscription_tier" class="admin-form-select">
                    <option value="free">Free</option>
                    <option value="starter">Starter</option>
                    <option value="professional">Professional</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>

            <div class="admin-form-checkbox-group">
                <input type="checkbox" id="subscription_active" name="subscription_active" value="1" class="admin-form-checkbox">
                <label for="subscription_active" class="admin-form-checkbox-label">Subscription Active</label>
            </div>

            <div class="admin-form-checkbox-group">
                <input type="checkbox" id="is_admin" name="is_admin" value="1" class="admin-form-checkbox">
                <label for="is_admin" class="admin-form-checkbox-label">Administrator</label>
            </div>

            <div class="admin-form-buttons">
                <button type="submit" class="admin-btn-submit">✓ Create User</button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn-cancel">✕ Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
