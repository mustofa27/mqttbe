@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">👥 User Management</h1>
            <p style="color: #9ca3af; font-size: 0.9rem;">Manage system users and permissions</p>
        </div>
        <a href="{{ route('admin.users.create') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.65rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); border: none;">
            ➕ Add New User
        </a>
    </div>

    @if (session('success'))
        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; color: #166534;">
            <strong>✓ Success!</strong> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; color: #991b1b;">
            <strong>✗ Error!</strong> {{ session('error') }}
        </div>
    @endif

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);">
            <h2 style="font-size: 1.1rem; font-weight: 600; color: #374151;">Active Users ({{ $users->total() }})</h2>
        </div>
        
        @if ($users->count() > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</th>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Email</th>
                        <th style="padding: 1rem 1.5rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Tier</th>
                        <th style="padding: 1rem 1.5rem; text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        <th style="padding: 1rem 1.5rem; text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f9fafb';" onmouseout="this.style.backgroundColor='white';">
                            <td style="padding: 1.25rem 1.5rem; color: #1f2937; font-weight: 500;">{{ $user->name }}</td>
                            <td style="padding: 1.25rem 1.5rem; color: #6b7280; font-size: 0.9rem;">{{ $user->email }}</td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: 
                                    @if($user->subscription_tier === 'enterprise') linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white;
                                    @elseif($user->subscription_tier === 'professional') linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;
                                    @elseif($user->subscription_tier === 'starter') linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;
                                    @else background: #f3f4f6; color: #6b7280;
                                    @endif
                                ">
                                    {{ ucfirst($user->subscription_tier) }}
                                </span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; text-align: center;">
                                @if ($user->is_admin)
                                    <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                        👑 Admin
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #e5e7eb; color: #6b7280;">
                                        User
                                    </span>
                                @endif
                                @if (!$user->subscription_active)
                                    <br>
                                    <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #fee2e2; color: #991b1b; margin-top: 0.25rem;">
                                        ⚠ Inactive
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1.25rem 1.5rem; text-align: center;">
                                <a href="{{ route('admin.users.edit', $user) }}" style="display: inline-block; padding: 0.5rem 1rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin: 0 0.25rem; transition: all 0.2s ease;">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="padding: 0.5rem 1rem; background: {{ $user->is_admin ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' }}; color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin: 0 0.25rem; cursor: pointer; transition: all 0.2s ease;">
                                        {{ $user->is_admin ? '👑 Remove' : '👑 Make' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete {{ $user->name }}? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin: 0 0.25rem; cursor: pointer; transition: all 0.2s ease;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 3rem 1.5rem; text-align: center; color: #9ca3af;">
                <p style="font-size: 1rem; margin-bottom: 0.5rem;">📭 No users found</p>
                <a href="{{ route('admin.users.create') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">Create the first user</a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if ($users->count() > 0)
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
