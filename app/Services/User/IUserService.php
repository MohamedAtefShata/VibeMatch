<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for user-related services
 */
interface IUserService
{
    /**
     * Get all users with pagination
     *
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated user results
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get a user by ID
     *
     * @param int $id User ID
     * @return User|null User data or null if not found
     */
    public function getUserById(int $id): ?User;

    /**
     * Get a user by email
     *
     * @param string $email User email
     * @return User|null User data or null if not found
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return User Created user
     */
    public function createUser(array $data): User;

    /**
     * Update an existing user
     *
     * @param User $user User to update
     * @param array $data Updated user data
     * @return bool Success status
     */
    public function updateUser(User $user, array $data): bool;

    /**
     * Delete a user
     *
     * @param User $user User to delete
     * @return bool Success status
     */
    public function deleteUser(User $user): bool;

    /**
     * Update a user's admin status
     *
     * @param User $user User to update
     * @param bool $isAdmin Whether the user should be an admin
     * @return bool Success status
     */
    public function updateAdminStatus(User $user, bool $isAdmin): bool;

    /**
     * Update a user's status (active, inactive, suspended)
     *
     * @param User $user User to update
     * @param string $status New status
     * @return bool Success status
     */
    public function updateUserStatus(User $user, string $status): bool;

}
