<?php

namespace App\Repositories\Interfaces;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RecommendationRepositoryInterface
{
    /**
     * Store a new recommendation
     *
     * @param array $data
     * @return Recommendation
     */
    public function create(array $data): Recommendation;

    /**
     * Get recommendations for a user
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getUserRecommendations(User $user, int $limit = 10): Collection;

    /**
     * Get recommendations for a user with pagination
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUserRecommendations(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Update a recommendation rating
     *
     * @param int $id
     * @param int $rating
     * @return bool
     */
    public function updateRating(int $id, int $rating): bool;

    /**
     * Get positively rated recommendations for a user
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getLikedRecommendations(User $user, int $limit = 10): Collection;

    /**
     * Get negatively rated recommendations for a user
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getDislikedRecommendations(User $user, int $limit = 10): Collection;
}
