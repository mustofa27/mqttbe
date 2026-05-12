@extends('layouts.app')

@section('title', 'Edit Add-on')

@section('content')
<div class="addon-form-page">
    <h1>Edit Add-on</h1>

    <form method="POST" action="{{ route('admin.subscription-addons.update', $addon) }}" class="addon-form">
        @csrf
        @method('PUT')
        @include('admin.subscription-addons.partials.form', ['addon' => $addon])
        <div class="addon-form-actions">
            <button type="submit">Update Add-on</button>
            <a href="{{ route('admin.subscription-addons.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
