@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-container">
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <h1>Reset Password</h1>
        <p class="auth-subtitle">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        @if (session('status'))
            <div class="auth-alert auth-alert-success">
                {{ session('status') }}
            </div>
        @endif

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

        <button type="submit" class="btn btn-primary">Send Reset Link</button>

        <div class="form-footer">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</div>
@endsection
