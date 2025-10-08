<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\WhatsAppService;
use App\Http\Controllers\AuthController;

// Public Auth Routes
Route::post('/login', [AuthController::class, 'apiLogin']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/user', [AuthController::class, 'apiUser']);

    // WhatsApp Routes
    Route::prefix('whatsapp')->group(function () {
        Route::get('/status', function (WhatsAppService $service) {
            return response()->json($service->getStatus());
        });

        Route::get('/check/{number}', function ($number, WhatsAppService $service) {
            return response()->json($service->checkNumber($number));
        });

        Route::post('/send', function (Request $request, WhatsAppService $service) {
            $request->validate([
                'number' => 'required|string|min:10',
                'message' => 'required|string|min:1',
            ]);

            return response()->json($service->sendMessage($request->number, $request->message));
        });

        Route::post('/disconnect', function (WhatsAppService $service) {
            return response()->json($service->disconnect());
        });
    });
});

// MCP (Model Context Protocol) Routes for AI Agents - Using API Key Authentication
Route::middleware(\App\Http\Middleware\ApiKeyMiddleware::class)->prefix('mcp')->group(function () {
    Route::get('/info', [App\Http\Controllers\MCPController::class, 'getServerInfo']);
    Route::get('/tools/list', [App\Http\Controllers\MCPController::class, 'listTools']);
    Route::post('/tools/call', [App\Http\Controllers\MCPController::class, 'callTool']);
    Route::get('/resources/list', [App\Http\Controllers\MCPController::class, 'listResources']);
    Route::post('/resources/read', [App\Http\Controllers\MCPController::class, 'getResource']);
});

// API Key Management is now handled by Livewire component in web interface
