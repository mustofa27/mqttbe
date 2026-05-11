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
                        <td>{{ $addon->unit_type }}</td>
                        <td>Rp {{ number_format($addon->price, 0, ',', '.') }}</td>
                        <td>{{ $addon->included_units }}</td>
                        <td>{{ $addon->is_recurring ? 'Yes' : 'No' }}</td>
                        <td>{{ $addon->active ? 'Active' : 'Inactive' }}</td>
                        <td class="actions">
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
                        <td colspan="8" class="empty-row">No add-ons found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.addon-admin-page { display: grid; gap: 1rem; }
.addon-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.addon-header h1 { margin: 0; }
.addon-header p { margin: 0.35rem 0 0; color: #666; }
.btn-primary { background: #2563eb; color: #fff; text-decoration: none; padding: 0.6rem 1rem; border-radius: 6px; font-weight: 600; }
.addon-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; }
.addon-table { width: 100%; border-collapse: collapse; }
.addon-table th, .addon-table td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #f1f5f9; }
.addon-table thead th { background: #f8fafc; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.03em; }
.actions { display: flex; gap: 0.75rem; align-items: center; }
.actions form { margin: 0; }
.actions button { border: none; background: transparent; color: #b91c1c; cursor: pointer; padding: 0; }
.actions a { color: #1d4ed8; text-decoration: none; }
.empty-row { text-align: center; color: #64748b; }
</style>
@endsection
