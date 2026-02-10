
@extends('layouts.app')
<style>
    .payment-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .payment-modal.active {
        display: flex;
    }
    .payment-modal-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        position: relative;
    }
</style>
@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700;">{{ $project->name }}</h1>
        <a href="{{ route('projects.index') }}" class="btn" style="background: #f3f4f6; color: #333; padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px;">Back</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="font-weight: 600; margin-bottom: 1rem;">Project Details</h3>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Status:</strong> <span style="background: {{ $project->active ? '#10b981' : '#ef4444' }}; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">{{ $project->active ? 'Active' : 'Inactive' }}</span>
            </p>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Project Key:</strong><br><code style="background: #f3f4f6; padding: 0.5rem; border-radius: 4px; font-size: 0.85rem; word-wrap: break-word; overflow-wrap: break-word; display: block; white-space: pre-wrap;">{{ $project->project_key }}</code>
            </p>
            <p style="color: #666; margin-bottom: 1rem; word-break: break-all;">
                <strong>Created:</strong> {{ $project->created_at->format('M d, Y') }}
            </p>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit Project</a>
            <button class="btn btn-warning" style="padding: 0.5rem 1rem; background: #f59e42; color: white; border: none; border-radius: 6px; margin-top: 1rem;" onclick="openSecretModal(); return false;">Regenerate Secret</button>

            <!-- Regenerate Secret Modal -->
            <div id="secretModal" class="payment-modal">
                <div class="payment-modal-content">
                    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="margin-bottom: 0;">Regenerate Project Secret</h2>
                        <button class="modal-close" onclick="closeSecretModal()" style="position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; background: none; border: none; color: #999; cursor: pointer;">×</button>
                    </div>
                    <form method="POST" action="{{ route('projects.regenerate-secret', $project) }}">
                        @csrf
                        <div class="form-group">
                            <label for="new_secret">New Secret</label>
                            <input type="text" name="new_secret" id="new_secret" required maxlength="255" placeholder="Enter new secret" style="background: #f8f9fa; width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #d1d5db;">
                        </div>
                        <button type="submit" class="btn btn-danger" style="width: 100%; padding: 1rem; margin-top: 1rem; background: #dc3545; color: white; border: none; border-radius: 6px;">Regenerate Secret</button>
                    </form>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="font-weight: 600; margin-bottom: 1rem;">Quick Stats</h3>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Devices:</strong> {{ $project->devices()->count() }}
            </p>
            <p style="color: #666; margin-bottom: 0.5rem;">
                <strong>Topics:</strong> {{ $project->topics()->count() }}
            </p>
            <p style="color: #666; margin-bottom: 1rem;">
                <strong>Last Updated:</strong> {{ $project->updated_at->format('M d, Y') }}
            </p>
            <a href="{{ route('devices.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Manage Devices</a>
        </div>
    </div>
@endsection

<script>
function openSecretModal() {
    document.getElementById('secretModal').classList.add('active');
}
function closeSecretModal() {
    document.getElementById('secretModal').classList.remove('active');
}
// Close modal when clicking outside
document.getElementById('secretModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSecretModal();
    }
});
</script>
