@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <h1>Login</h1>

        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                    placeholder="you@example.com"
                >
            </div>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Enter your password"
                    style="padding-right: 2.5rem;"
                >
                <button type="button" class="password-toggle-btn" data-toggle="password" onclick="togglePasswordVisibility('password')">👁️</button>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div style="text-align: right; margin-bottom: 1rem;">
            <a href="{{ route('password.request') }}" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                Forgot your password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>

        <div class="form-footer">
            Don't have an account? <a href="{{ route('register') }}">Register here</a>
        </div>
    </form>
</div>
@endsection
