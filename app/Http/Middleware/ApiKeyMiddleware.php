<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // Look for API Key in header or parameters
        $apiKey = $request->header('X-API-Key') 
                 ?? $request->header('Authorization') 
                 ?? $request->input('api_key');

        // Remove Bearer prefix if present
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide a valid API key in X-API-Key header or api_key parameter'
            ], 401);
        }

        // Find the key in database
        $keyModel = ApiKey::where('key', $apiKey)->first();

        if (!$keyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is not valid'
            ], 401);
        }

        if (!$keyModel->isValid()) {
            return response()->json([
                'error' => 'API key expired or inactive',
                'message' => 'The provided API key is expired or has been deactivated'
            ], 401);
        }

        // Check permissions if required
        if ($permission && !$keyModel->hasPermission($permission)) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'message' => "This API key does not have permission for: {$permission}"
            ], 403);
        }

        // Update last used timestamp
        $keyModel->markAsUsed();

        // Add key information to request
        $request->merge(['api_key_model' => $keyModel]);

        return $next($request);
    }
}
