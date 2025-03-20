<?php

use App\Http\Controllers\Recommendation\RecommendationController;
use Illuminate\Support\Facades\Route;

// Recommendation views
Route::middleware(['auth'])->group(function () {
    Route::get('/recommendations/history', [RecommendationController::class, 'history'])
        ->name('recommendations.history');
    Route::get('/recommendations/personalized', [RecommendationController::class, 'personalized'])
        ->name('recommendations.personalized');
});

// Recommendation-related API endpoints - using Inertia
Route::middleware(['auth'])->prefix('api/recommendations')->name('api.recommendations.')->group(function () {
    Route::get('/history', [RecommendationController::class, 'history'])->name('history');
    Route::post('/', [RecommendationController::class, 'store'])->name('store');
    Route::put('/{id}/rate', [RecommendationController::class, 'rate'])->name('rate');
    Route::get('/personalized', [RecommendationController::class, 'personalized'])->name('personalized');
});
