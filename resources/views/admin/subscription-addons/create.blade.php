@extends('layouts.app')

@section('title', 'Create Add-on')

@section('content')
<div class="addon-form-page">
    <h1>Create Add-on</h1>

    <form method="POST" action="{{ route('admin.subscription-addons.store') }}" class="addon-form">
        @csrf
        @include('admin.subscription-addons.partials.form', ['addon' => null])
        <div class="addon-form-actions">
            <button type="submit">Save Add-on</button>
            <a href="{{ route('admin.subscription-addons.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
