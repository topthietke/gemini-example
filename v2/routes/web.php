<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('chat.index');
});

// =====================================================
// Chat Routes
// =====================================================
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

// =====================================================
// Video Routes
// =====================================================
Route::get('/video', [VideoController::class, 'index'])->name('video.index');

// =====================================================
// API Routes (no auth for demo - add middleware in prod)
// =====================================================
Route::prefix('api')->group(function () {

    // Gemini Chat
    Route::prefix('chat')->group(function () {
        Route::post('/message', [ChatController::class, 'sendMessage'])->name('api.chat.message');
        Route::post('/upload',  [ChatController::class, 'sendWithFile'])->name('api.chat.upload');
        Route::post('/stream',  [ChatController::class, 'streamMessage'])->name('api.chat.stream');
    });

    // Gemini Models
    Route::get('/models', [ChatController::class, 'listModels'])->name('api.models');

    // Text-to-Video
    Route::prefix('video')->group(function () {
        Route::post('/generate',        [VideoController::class, 'generate'])->name('api.video.generate');
        Route::get('/status/{operation}', [VideoController::class, 'checkStatus'])->name('api.video.status')
            ->where('operation', '.*');
    });
});
