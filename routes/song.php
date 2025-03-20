<?php

use App\Http\Controllers\Song\SongController;
use App\Http\Controllers\Song\RecommendationController;
use Illuminate\Support\Facades\Route;

// Web routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('songs')->name('songs.')->group(function () {
        Route::get('/', [SongController::class, 'index'])->name('index');
        Route::get('/{id}', [SongController::class, 'show'])->name('show');
    });
});

Route::prefix('api')->group(function () {
    // Song-related API endpoints
    Route::prefix('songs')->name('api.songs.')->group(function () {
        Route::get('/search', [SongController::class, 'search'])->name('search');
        Route::post('/recommend', [SongController::class, 'recommend'])->name('recommend');

        // Add other publicly accessible song endpoints here

        // Protected song endpoints
        Route::middleware(['auth'])->group(function () {
            Route::get('/personalized', [RecommendationController::class, 'personalizedRecommendations'])->name('personalized');
            Route::get('/previous-recommendations', [SongController::class, 'previousRecommendations'])->name('previous-recommendations');
        });

        // Admin-only song management API endpoints
        Route::middleware(['auth', 'admin'])->group(function () {
            Route::post('/', [SongController::class, 'store'])->name('store');
            Route::put('/{id}', [SongController::class, 'update'])->name('update');
            Route::delete('/{id}', [SongController::class, 'destroy'])->name('destroy');
        });
    });
});
