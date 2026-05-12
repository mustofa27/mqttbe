@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="auth-container">
    <div class="auth-form auth-center">
        <div class="verify-icon">📧</div>
        <h1>Verify Your Email</h1>
        <p class="auth-subtitle">
            Thanks for registering! Before you get started, please verify your email address by clicking the link we just sent you.
        </p>

        @if (session('success'))
            <div class="auth-alert auth-alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="stack-gap-sm">
            @csrf
            <button type="submit" class="btn btn-primary">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-button auth-link-btn">
                Log Out
            </button>
        </form>
    </div>
</div>
@endsection
