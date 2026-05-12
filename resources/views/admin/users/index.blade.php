@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div class="admin-header">
        <div>
            <h1 class="admin-header-title">👥 User Management</h1>
            <p class="admin-header-subtitle">Manage system users and permissions</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-admin-action">
            ➕ Add New User
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <strong>✓ Success!</strong> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error">
            <strong>✗ Error!</strong> {{ session('error') }}
        </div>
    @endif

    <div class="table-card">
        <div class="table-card-header">
            <h2>Active Users ({{ $users->total() }})</h2>
        </div>
        
        @if ($users->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tier</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="table-email">{{ $user->email }}</td>
                            <td>
                                <span class="badge-tier badge-tier-{{ $user->subscription_tier }}">
                                    {{ ucfirst($user->subscription_tier) }}
                                </span>
                            </td>
                            <td class="action-cell">
                                @if ($user->is_admin)
                                    <span class="badge-admin">👑 Admin</span>
                                @else
                                    <span class="badge-user">User</span>
                                @endif
                                @if (!$user->subscription_active)
                                    <br>
                                    <span class="badge-inactive">⚠ Inactive</span>
                                @endif
                            </td>
                            <td class="action-cell">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-admin btn-admin-edit">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="form-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-admin btn-admin-toggle {{ $user->is_admin ? 'active' : '' }}">
                                        {{ $user->is_admin ? '👑 Remove' : '👑 Make' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="form-inline" onsubmit="return confirm('Delete {{ $user->name }}? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-admin btn-admin-delete">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-table-state">
                <p>📭 No users found</p>
                <a href="{{ route('admin.users.create') }}" class="empty-table-link">Create the first user</a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if ($users->count() > 0)
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection