@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="auth-container">
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <h1>Create Account</h1>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}"
                required 
                autofocus
                placeholder="John Doe"
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
                value="{{ old('email') }}"
                required
                placeholder="you@example.com"
            >
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
                    placeholder="Minimum 8 characters"
                    style="padding-right: 2.5rem;"
                >
                <button type="button" class="password-toggle-btn" data-toggle="password" onclick="togglePasswordVisibility('password')">👁️</button>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <div class="password-toggle-wrapper">
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    placeholder="Confirm your password"
                    style="padding-right: 2.5rem;"
                >
                <button type="button" class="password-toggle-btn" data-toggle="password_confirmation" onclick="togglePasswordVisibility('password_confirmation')">👁️</button>
            </div>
            @error('password_confirmation')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>

        <div class="form-footer">
            Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
    </form>
</div>
@endsection
