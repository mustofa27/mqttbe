@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-container">
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <h1>Create New Password</h1>
        <p class="auth-subtitle">
            Enter your new password below.
        </p>

        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ $email ?? old('email') }}"
                    required
                    readonly
                    class="input-readonly"
                >
            </div>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Minimum 8 characters"
                >
                <button type="button" class="password-toggle-btn" data-toggle="password" onclick="togglePasswordVisibility('password')">👁️</button>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    placeholder="Confirm your password"
                >
                <button type="button" class="password-toggle-btn" data-toggle="password_confirmation" onclick="togglePasswordVisibility('password_confirmation')">👁️</button>
            </div>
            @error('password_confirmation')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Reset Password</button>

        <div class="form-footer">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</div>
@endsection
