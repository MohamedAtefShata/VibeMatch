<?php

namespace App\Policies;

use App\Models\Song;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SongPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Everyone can view songs
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Song $song): bool
    {
        return true; // Everyone can view a song
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Only admins can create songs
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Song $song): bool
    {
        return $user->isAdmin(); // Only admins can update songs
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Song $song): bool
    {
        return $user->isAdmin(); // Only admins can delete songs
    }
}
