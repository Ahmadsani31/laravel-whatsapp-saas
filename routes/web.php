<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WhatsAppWebhookController;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('/api-keys', App\Livewire\ApiKeyManager::class)->name('api-keys');
    Route::get('/campaigns', App\Livewire\CampaignManager::class)->name('campaigns');
    Route::get('/campaigns/{id}', App\Livewire\CampaignDetails::class)->name('campaigns.details');
    Route::get('/campaigns/{id}/replies', App\Livewire\CampaignReplies::class)->name('campaigns.replies');
    Route::get('/campaigns/{id}/auto-replies', App\Livewire\AutoReplyManager::class)->name('campaigns.auto-replies');


    // Theme preference
    Route::post('/theme', function (Illuminate\Http\Request $request) {
        $theme = $request->input('theme', 'light');

        if (in_array($theme, ['light', 'dark', 'auto'])) {
            session(['theme' => $theme]);
            return response()->json(['success' => true, 'theme' => $theme]);
        }

        return response()->json(['success' => false], 400);
    })->name('theme.update');
});

// WhatsApp Webhook Routes (public - no auth required)
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verifyWebhook']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handleWebhook']);
