<?php

namespace App\Http\Controllers\Api;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $keys = ApiKey::where('user_id', $user->id)
            ->select(['id', 'name', 'key', 'is_active', 'last_used_at', 'expires_at', 'created_at'])
            ->paginate(10);

        return response()->json([
            'data' => $keys->items(),
            'pagination' => [
                'total' => $keys->total(),
                'per_page' => $keys->perPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $key = Str::random(32);
        $secret = Str::random(64);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'key' => hash('sha256', $key),
            'secret' => hash('sha256', $secret),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key' => $key, // Only shown once at creation
                'secret' => $secret, // Only shown once at creation
                'message' => 'Save your API key and secret. You will not be able to see them again.',
            ],
        ], 201);
    }

    public function destroy(Request $request, ApiKey $apiKey)
    {
        $user = $request->user();

        if ($apiKey->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $apiKey->delete();

        return response()->json(['message' => 'API key deleted successfully']);
    }

    public function deactivate(Request $request, ApiKey $apiKey)
    {
        $user = $request->user();

        if ($apiKey->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $apiKey->update(['is_active' => false]);

        return response()->json(['data' => $apiKey]);
    }
}
