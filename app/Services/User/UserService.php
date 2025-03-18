<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service for user-related operations
 */
class UserService implements IUserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated user results
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->userRepository->all();
    }

    /**
     * Get a user by ID
     *
     * @param int $id User ID
     * @return User|null User data or null if not found
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Get a user by email
     *
     * @param string $email User email
     * @return User|null User data or null if not found
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return User Created user
     */
    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * Update an existing user
     *
     * @param User $user User to update
     * @param array $data Updated user data
     * @return bool Success status
     */
    public function updateUser(User $user, array $data): bool
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Delete a user
     *
     * @param User $user User to delete
     * @return bool Success status
     */
    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    /**
     * Update a user's admin status
     *
     * @param User $user User to update
     * @param bool $isAdmin Whether the user should be an admin
     * @return bool Success status
     */
    public function updateAdminStatus(User $user, bool $isAdmin): bool
    {
        return $this->userRepository->update($user, [
            'role' => $isAdmin ? 'admin' : 'user'
        ]);
    }

    /**
     * Update a user's status (active, inactive, suspended)
     *
     * @param User $user User to update
     * @param string $status New status
     * @return bool Success status
     */
    public function updateUserStatus(User $user, string $status): bool
    {
        // This assumes your User model has a 'status' field
        // If not, modify this according to your application's implementation
        return $this->userRepository->update($user, ['status' => $status]);
    }
}
