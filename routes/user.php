<?php

use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Route;

// User profile routes
Route::middleware(['auth'])->prefix('api/user')->name('api.user.')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'profile'])->name('profile');
});
