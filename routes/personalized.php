<?php

use App\Http\Controllers\PersonalizedController;
use Illuminate\Support\Facades\Route;

// Personalized recommendation routes
Route::middleware(['auth'])->group(function () {
    // Main personalized dashboard
    Route::get('/personalized', [PersonalizedController::class, 'index'])
        ->name('personalized');

    // API routes for personalized features
    Route::prefix('api/personalized')->name('api.personalized.')->group(function () {
        // Get recommendation history
        Route::get('/history', [PersonalizedController::class, 'history'])
            ->name('history');

        // Rate a recommendation
        Route::post('/rate/{id}', [PersonalizedController::class, 'rateRecommendation'])
            ->name('rate');

        // Store a new recommendation
        Route::post('/store', [PersonalizedController::class, 'storeRecommendation'])
            ->name('store');
    });
});
