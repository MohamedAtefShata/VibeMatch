<?php

namespace App\Services\Profile;

use App\Models\User;

interface IProfileService
{
    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updateProfile(User $user, array $data): bool;

    /**
     * Update user password
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function updatePassword(User $user, string $password): bool;

    /**
     * Delete user account
     *
     * @param User $user
     * @return bool
     */
    public function deleteAccount(User $user): bool;
}
