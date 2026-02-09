@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0; max-width: 600px;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">➕ Create New User</h1>
        <p style="color: #9ca3af; font-size: 0.9rem;">Add a new user to the system</p>
    </div>

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 2rem; border: 1px solid #e5e7eb;">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label for="name" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease; background: white;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)';" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                @error('name')
                    <p style="color: #dc2626; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease; background: white;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)';" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                @error('email')
                    <p style="color: #dc2626; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease; background: white;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)';" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                @error('password')
                    <p style="color: #dc2626; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="password_confirmation" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease; background: white;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)';" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="subscription_tier" style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;">Subscription Tier</label>
                <select id="subscription_tier" name="subscription_tier" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: inherit; transition: all 0.2s ease; background: white; cursor: pointer;">
                    <option value="free">Free</option>
                    <option value="starter">Starter</option>
                    <option value="professional">Professional</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <input type="checkbox" id="subscription_active" name="subscription_active" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                <label for="subscription_active" style="color: #374151; font-weight: 500; cursor: pointer; margin: 0;">Subscription Active</label>
            </div>

            <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <input type="checkbox" id="is_admin" name="is_admin" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                <label for="is_admin" style="color: #374151; font-weight: 500; cursor: pointer; margin: 0;">Administrator</label>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" style="flex: 1; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 1rem;">
                    ✓ Create User
                </button>
                <a href="{{ route('admin.users.index') }}" style="flex: 1; padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 1rem; text-align: center; text-decoration: none;">
                    ✕ Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
