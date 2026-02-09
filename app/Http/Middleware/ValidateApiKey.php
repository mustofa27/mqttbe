<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        $apiKey = \App\Models\ApiKey::where('key', $token)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json(['error' => 'API key has expired'], 401);
        }

        // Update last used time
        $apiKey->update(['last_used_at' => now()]);

        // Attach user to request
        $request->setUserResolver(fn () => $apiKey->user);

        return $next($request);
    }
}
