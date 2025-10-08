<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HandleLivewireErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Livewire Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If it's a Livewire request, return a proper error response
            if ($request->header('X-Livewire')) {
                return response()->json([
                    'error' => 'حدث خطأ في النظام',
                    'message' => $e->getMessage()
                ], 500);
            }
            
            throw $e;
        }
    }
}