<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Create an error handler route
Route::get('/error', function() {
    return Inertia::render('Error', [
        'status' => session('status', 500),
        'message' => session('message', 'An error occurred')
    ]);
})->name('error');

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('personalized', [DashboardController::class, 'personalized'])->name('personalized');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/song.php';
require __DIR__.'/recommendation.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
