<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    // Check if user is admin
    $isAdmin = auth()->user()->isAdmin();

    if ($isAdmin) {
        // Redirect admin users to the admin dashboard
        return redirect()->route('admin.songs.index');
    }

    // Regular users see the normal dashboard
    return Inertia::render('dashboard/Dashboard', [
        'isAdmin' => false
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Personalized page for regular users
Route::get('personalized', function () {
    // Check if user is admin - admins should not see this page
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.songs.index');
    }

    return Inertia::render('Personalized', [
        'isAdmin' => false
    ]);
})->middleware(['auth', 'verified'])->name('personalized');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/song.php';
require __DIR__.'/recommendation.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
