<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class ProfileService implements IProfileService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updateProfile(User $user, array $data): bool
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Update user password
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function updatePassword(User $user, string $password): bool
    {
        return $this->userRepository->update($user, [
            'password' => $password
        ]);
    }

    /**
     * Delete user account
     *
     * @param User $user
     * @return bool
     */
    public function deleteAccount(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
}
