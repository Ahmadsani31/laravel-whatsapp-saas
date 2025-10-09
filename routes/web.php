<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

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
