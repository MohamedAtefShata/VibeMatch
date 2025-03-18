<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin songs management dashboard
    Route::get('/songs', function () {
        try {
            $songService = app(App\Services\Song\ISongService::class);
            $perPage = 15; // Number of items per page
            $songs = $songService->getAllSongs($perPage);

            // Make sure isAdmin is explicitly set to true here
            return Inertia::render('admin/AdminDashboard', [
                'songs' => $songs,
                'isAdmin' => true // This must be passed to the view
            ]);
        } catch (\Exception $e) {
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
                'isAdmin' => true // This must be passed to the view
            ]);
        }
    })->name('songs.index');

    // Admin user management dashboard
    Route::get('/users', function () {
        try {

            $userService = app(App\Services\User\IUserService::class);
            $perPage = 15;

            $users = $userService->getAllUsers($perPage);

            // Add is_admin property to each user
            $users->getCollection()->transform(function ($user) {
                $user->is_admin = $user->isAdmin();
                return $user;
            });

            return Inertia::render('admin/AdminUserManagement', [
                'users' => $users,
                'isAdmin' => true // This must be passed to the view
            ]);
        } catch (\Exception $e) {
            return Inertia::render('admin/AdminUserManagement', [
                'users' => [
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
                'isAdmin' => true // This must be passed to the view
            ]);
        }
    })->name('users.index');
});

// API routes for user management (admin only)
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::prefix('users')->name('api.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/status', [UserController::class, 'updateStatus'])->name('update-status');
        Route::put('/{id}/role', [UserController::class, 'updateRole'])->name('update-role');
    });
});
