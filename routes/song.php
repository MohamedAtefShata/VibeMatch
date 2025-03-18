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
    Route::prefix('songs')->name('api.songs.')->group(function () {
        Route::get('/search', [SongController::class, 'search'])->name('search');
        Route::post('/recommend', [SongController::class, 'recommend'])->name('recommend');
        Route::get('/previous-recommendations', [SongController::class, 'previousRecommendations'])->name('previous-recommendations');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::prefix('songs')->name('api.songs.')->group(function () {
            Route::post('/', [SongController::class, 'store'])->name('store');
            Route::put('/{id}', [SongController::class, 'update'])->name('update');
            Route::delete('/{id}', [SongController::class, 'destroy'])->name('destroy');
        });
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin songs management dashboard
    Route::get('/songs', function () {
        try {
            $songService = app(App\Services\Song\ISongService::class);
            $perPage = 15; // Number of items per page
            $songs = $songService->getAllSongs($perPage);

            // Ensure 'songs' has the expected structure for pagination
            return Inertia::render('admin/AdminDashboard', [
                'songs' => $songs,
                'isAdmin' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading admin dashboard: ' . $e->getMessage());

            // Provide a minimal structured object to prevent component errors
            return Inertia::render('admin/AdminDashboard', [
                'songs' => [
                    'current_page' => 1,
                    'data' => [],
                    'links' => [],
                    'total' => 0,
                    'per_page' => 15,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                    'first_page_url' => '',
                    'last_page_url' => '',
                    'next_page_url' => null,
                    'prev_page_url' => null,
                    'path' => ''
                ],
                'isAdmin' => true
            ]);
        }
    })->name('songs.index');
});
