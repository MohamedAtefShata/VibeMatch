<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    // Check if user is admin
    if (auth()->user()->isAdmin()) {
        // Redirect admin users to the admin dashboard
        return redirect()->route('admin.songs.index');
    }

    // Regular users see the normal dashboard
    return Inertia::render('dashboard/Dashboard', [
        'isAdmin' => auth()->user()->isAdmin()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/song.php';
