@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="auth-container">
    <div class="auth-form" style="text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
        <h1 style="margin-bottom: 0.5rem;">Verify Your Email</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 0.95rem;">
            Thanks for registering! Before you get started, please verify your email address by clicking the link we just sent you.
        </p>

        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom: 1rem;">
            @csrf
            <button type="submit" class="btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; cursor: pointer; border: none; border-radius: 8px;">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: none; border: none; color: #6b7280; font-size: 0.9rem; cursor: pointer; text-decoration: underline;">
                Log Out
            </button>
        </form>
    </div>
</div>
@endsection
