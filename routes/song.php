<?php

use App\Http\Controllers\Song\SongController;
use Illuminate\Support\Facades\Route;

// Web routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('songs')->name('songs.')->group(function () {
        Route::get('/', [SongController::class, 'index'])->name('index');
        Route::get('/{id}', [SongController::class, 'show'])->name('show');
    });
});

Route::prefix('api')->group(function () {
    Route::prefix('songs')->name('api.songs.')->group(function () {
        Route::get('/search', [SongController::class, 'search'])->name('search');
        Route::post('/recommend', [SongController::class, 'recommend'])->name('recommend');
        Route::get('/previous-recommendations', [SongController::class, 'previousRecommendations'])->name('previous-recommendations');
    });

    // Admin routes - protected with admin middleware
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::prefix('songs')->name('api.songs.')->group(function () {
            Route::post('/', [SongController::class, 'store'])->name('store');
            Route::put('/{id}', [SongController::class, 'update'])->name('update');
            Route::delete('/{id}', [SongController::class, 'destroy'])->name('destroy');
        });
    });
});
