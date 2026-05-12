@extends('layouts.app')

@section('title', 'Subscription Add-ons')

@section('content')
<div class="addon-admin-page">
    <div class="addon-header">
        <div>
            <h1>Subscription Add-ons</h1>
            <p>Manage purchasable add-on catalog items.</p>
        </div>
        <a href="{{ route('admin.subscription-addons.create') }}" class="btn-primary">Create Add-on</a>
    </div>

    <div class="addon-table-wrap">
        <table class="addon-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Unit Type</th>
                    <th>Price</th>
                    <th>Included Units</th>
                    <th>Recurring</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addons as $addon)
                    <tr>
                        <td>{{ $addon->code }}</td>
                        <td>{{ $addon->name }}</td>
                        <td>{{ $addon->unit_type_label }}</td>
                        <td>Rp {{ number_format($addon->price, 0, ',', '.') }}</td>
                        <td>{{ $addon->included_units }}</td>
                        <td>{{ $addon->is_recurring ? 'Yes' : 'No' }}</td>
                        <td>{{ $addon->active ? 'Active' : 'Inactive' }}</td>
                        <td class="addon-actions">
                            <a href="{{ route('admin.subscription-addons.edit', $addon) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.subscription-addons.destroy', $addon) }}" onsubmit="return confirm('Delete this add-on?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="addon-empty-row">No add-ons found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
