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
                >
                <button type="button" class="password-toggle-btn" data-toggle="password" onclick="togglePasswordVisibility('password')">👁️</button>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-divider">or</div>

        <div class="google-auth-wrap">
            <a href="{{ route('google.redirect') }}" class="btn btn-google">
                <span class="btn-google-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" width="18" height="18" role="img" focusable="false">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.655 32.657 29.193 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.046 6.053 29.27 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.046 6.053 29.27 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.169 0 9.86-1.977 13.409-5.191l-6.19-5.238C29.146 35.091 26.678 36 24 36c-5.173 0-9.625-3.328-11.287-7.946l-6.522 5.025C9.49 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.043 12.043 0 0 1-4.084 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                </span>
                <span class="btn-google-text">Continue with Google</span>
            </a>
        </div>

        <div class="auth-helper">
            <a href="{{ route('password.request') }}">
                Forgot your password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>

        <div class="form-footer">
            Don't have an account? <a href="{{ route('register') }}">Register here</a>
        </div>
    </form>
</div>
@endsection
