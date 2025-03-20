<?php

use App\Http\Controllers\Recommendation\RecommendationController;
use Illuminate\Support\Facades\Route;

// Recommendation-related API endpoints
Route::middleware(['auth'])->prefix('api/recommendations')->name('api.recommendations.')->group(function () {
    Route::get('/history', [RecommendationController::class, 'history'])->name('history');
    Route::post('/', [RecommendationController::class, 'store'])->name('store');
    Route::put('/{id}/rate', [RecommendationController::class, 'rate'])->name('rate');
    Route::get('/personalized', [RecommendationController::class, 'personalized'])->name('personalized');
});
