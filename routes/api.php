<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Cloudflare Webhook Routes (public - no CSRF required)
Route::post('/webhooks/cloudflare/video', [\App\Http\Controllers\CloudflareWebhookController::class, 'handleVideoWebhook'])->name('webhooks.cloudflare.video');
use App\Http\Controllers\MovieController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::resource('movies', MovieController::class)->only([
    'store'
]);

// FCM notification routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/notifications/manual', [\App\Http\Controllers\NotificationController::class, 'sendManual']);
    Route::post('/user/fcm-token', [\App\Http\Controllers\NotificationController::class, 'updateToken']);
});
