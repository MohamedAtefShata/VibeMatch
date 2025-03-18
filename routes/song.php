<?php

use App\Http\Controllers\Song\SongController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
        Route::get('/previous-recommendations', [SongController::class, 'previousRecommendations'])->name('previous-recommendations');

        // New endpoint for personalized recommendations, using existing methods
        Route::middleware(['auth'])->get('/personalized', function() {
            $songService = app(App\Services\Song\ISongService::class);
            $userId = auth()->id();

            // Use the existing recommendation methods
            $forYou = [];
            $basedOnGenre = [];

            // For demo purposes, we'll use a hardcoded song ID to get recommendations
            // In a real implementation, this would be based on user's history
            if ($userId) {
                try {
                    $forYou = $songService->getRecommendationsForSong(1, 5)->toArray();

                    // Get recommendations based on multiple songs (user's favorites)
                    // This is just a placeholder - in a real app, you'd get the user's favorite songs
                    $userFavoriteSongs = [1, 2, 3];
                    $basedOnGenre = $songService->getRecommendationsForMultipleSongs($userFavoriteSongs, 5)->toArray();
                } catch (\Exception $e) {
                    \Log::error('Error getting personalized recommendations: ' . $e->getMessage());
                }
            }

            return response()->json([
                'forYou' => $forYou,
                'basedOnGenre' => $basedOnGenre,
                'newReleases' => [] // Placeholder for new releases
            ]);
        })->name('personalized');
    });

    // User profile API endpoints - using existing Profile service
    Route::middleware(['auth'])->prefix('user')->name('api.user.')->group(function () {
        Route::get('/profile', function() {
            $user = auth()->user();

            // Build a basic profile response
            // In a real implementation, you'd get this data from a proper user service
            return response()->json([
                'favoriteGenres' => [], // This would come from user's listening history
                'recentPlays' => [],   // This would come from a plays/history table
                'topArtists' => []     // This would be derived from listening history
            ]);
        })->name('profile');
    });

    // Admin-only song management API endpoints
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::prefix('songs')->name('api.songs.')->group(function () {
            Route::post('/', [SongController::class, 'store'])->name('store');
            Route::put('/{id}', [SongController::class, 'update'])->name('update');
            Route::delete('/{id}', [SongController::class, 'destroy'])->name('destroy');
        });
    });
});
