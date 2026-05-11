<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Services\PlanEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        $keys = ApiKey::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.api-keys', compact('keys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $user = auth()->user();
        $limits = $user->getSubscriptionLimits();
        $maxApiKeys = (int) ($limits['max_api_keys'] ?? 0);
        $activeKeys = ApiKey::where('user_id', $user->id)->where('is_active', true)->count();

        if ($maxApiKeys !== -1 && $activeKeys >= $maxApiKeys) {
            $shouldBlock = app(PlanEnforcementService::class)->shouldBlock('api_key_limit', [
                'user_id' => $user->id,
                'current' => $activeKeys,
                'limit' => $maxApiKeys,
            ]);

            if (!$shouldBlock) {
                // Continue in grace period (soft enforcement)
            } else {
                return redirect()->back()->with('error', "API key limit reached for your plan ({$maxApiKeys} active keys).");
            }
        }

        $key = Str::random(32);
        $secret = Str::random(64);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'key' => hash('sha256', $key),
            'secret' => hash('sha256', $secret),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('api-keys.show', $apiKey->id)
            ->with('apiKey', ['key' => $key, 'secret' => $secret])
            ->with('success', 'API key created successfully. Save it now as you won\'t be able to see it again!');
    }

    public function show(ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        return view('dashboard.api-keys-show', compact('apiKey'));
    }

    public function destroy(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        $apiKey->delete();

        return redirect()->route('api-keys.index')
            ->with('success', 'API key deleted successfully.');
    }

    public function deactivate(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        $apiKey->update(['is_active' => false]);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key deactivated successfully.');
    }

    public function activate(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403);
        }

        $apiKey->update(['is_active' => true]);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key activated successfully.');
    }
}
