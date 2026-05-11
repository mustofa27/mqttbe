@extends('layouts.app')

@section('title', 'Create Add-on')

@section('content')
<div class="addon-form-page">
    <h1>Create Add-on</h1>

    <form method="POST" action="{{ route('admin.subscription-addons.store') }}" class="addon-form">
        @csrf
        @include('admin.subscription-addons.partials.form', ['addon' => null])
        <div class="actions">
            <button type="submit">Save Add-on</button>
            <a href="{{ route('admin.subscription-addons.index') }}">Cancel</a>
        </div>
    </form>
</div>

<style>
.addon-form-page { display: grid; gap: 1rem; max-width: 720px; }
.addon-form { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.25rem; display: grid; gap: 1rem; }
.actions { display: flex; gap: 0.75rem; }
.actions button { background: #2563eb; color: #fff; border: none; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; }
.actions a { padding: 0.6rem 1rem; text-decoration: none; color: #334155; border: 1px solid #cbd5e1; border-radius: 6px; }
</style>
@endsection
