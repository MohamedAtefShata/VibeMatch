<?php

namespace App\Services\Recommendation;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface IRecommendationService
{
    /**
     * Store a new recommendation
     *
     * @param int $userId
     * @param int $songId
     * @param array $sourceSongIds
     * @return Recommendation
     */
    public function storeRecommendation(int $userId, int $songId, array $sourceSongIds): Recommendation;

    /**
     * Rate a recommendation
     *
     * @param int $recommendationId
     * @param int $rating
     * @return bool
     */
    public function rateRecommendation(int $recommendationId, int $rating): bool;

    /**
     * Get user's recommendation history
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getUserRecommendationHistory(User $user, int $limit = 10): Collection;

    /**
     * Get user's recommendation history with pagination
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUserRecommendationHistory(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get personalized recommendations based on user's ratings
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 5): Collection;

    /**
     * Get recommendations based on genres the user likes
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getGenreBasedRecommendations(User $user, int $limit = 5): Collection;
}
