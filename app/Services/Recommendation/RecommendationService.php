<?php

namespace App\Services\Recommendation;

use App\Models\Recommendation;
use App\Models\Song;
use App\Models\User;
use App\Repositories\Interfaces\RecommendationRepositoryInterface;
use App\Repositories\Interfaces\SongRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService implements IRecommendationService
{
    /**
     * @var RecommendationRepositoryInterface
     */
    protected $recommendationRepository;

    /**
     * @var SongRepositoryInterface
     */
    protected $songRepository;

    /**
     * Create a new service instance.
     *
     * @param RecommendationRepositoryInterface $recommendationRepository
     * @param SongRepositoryInterface $songRepository
     */
    public function __construct(
        RecommendationRepositoryInterface $recommendationRepository,
        SongRepositoryInterface $songRepository
    ) {
        $this->recommendationRepository = $recommendationRepository;
        $this->songRepository = $songRepository;
    }

    /**
     * Store a new recommendation
     *
     * @param int $userId
     * @param int $songId
     * @param array $sourceSongIds
     * @return Recommendation
     */
    public function storeRecommendation(int $userId, int $songId, array $sourceSongIds): Recommendation
    {
        return $this->recommendationRepository->create([
            'user_id' => $userId,
            'song_id' => $songId,
            'source_song_ids' => $sourceSongIds,
            'rating' => null
        ]);
    }

    /**
     * Rate a recommendation
     *
     * @param int $recommendationId
     * @param int $rating
     * @return bool
     */
    public function rateRecommendation(int $recommendationId, int $rating): bool
    {
        // Clear user's recommendation cache when they rate a song
        $recommendation = Recommendation::findOrFail($recommendationId);
        $cacheKey = "user_{$recommendation->user_id}_personalized_recommendations";
        Cache::forget($cacheKey);

        return $this->recommendationRepository->updateRating($recommendationId, $rating);
    }

    /**
     * Get user's recommendation history
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getUserRecommendationHistory(User $user, int $limit = 10): Collection
    {
        return $this->recommendationRepository->getUserRecommendations($user, $limit);
    }

    /**
     * Get user's recommendation history with pagination
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUserRecommendationHistory(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->recommendationRepository->getPaginatedUserRecommendations($user, $perPage);
    }

    /**
     * Get personalized recommendations based on user's ratings
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 5): Collection
    {
        $cacheKey = "user_{$user->id}_personalized_recommendations";
        $cacheTtl = 60 * 24; // Cache for 24 hours

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $limit) {
            // Get songs the user has liked
            $likedRecommendations = $this->recommendationRepository->getLikedRecommendations($user);

            if ($likedRecommendations->isEmpty()) {
                // If user hasn't liked any recommendations yet, return random songs
                return $this->songRepository->all($limit)->getCollection();
            }

            // Get the song IDs the user liked
            $likedSongIds = $likedRecommendations->pluck('song_id')->toArray();

            if (count($likedSongIds) === 1) {
                // If user has only liked one song, find similar songs to that one
                $song = $this->songRepository->find($likedSongIds[0]);
                return $this->songRepository->findSimilarToSong($song, $limit);
            }

            // Get all the liked songs to find similar ones
            $likedSongs = new Collection();
            foreach ($likedSongIds as $songId) {
                $likedSongs->push($this->songRepository->find($songId));
            }

            // Find songs similar to the liked songs
            return $this->songRepository->findSimilarToMultipleSongs($likedSongs, $limit);
        });
    }

    /**
     * Get recommendations based on genres the user likes
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getGenreBasedRecommendations(User $user, int $limit = 5): Collection
    {
        $cacheKey = "user_{$user->id}_genre_recommendations";
        $cacheTtl = 60 * 24; // Cache for 24 hours

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $limit) {
            // Get songs the user has liked
            $likedRecommendations = $this->recommendationRepository->getLikedRecommendations($user);

            if ($likedRecommendations->isEmpty()) {
                // If user hasn't liked any recommendations yet, return random songs
                return $this->songRepository->all($limit)->getCollection();
            }

            // Extract genres from liked songs
            $likedGenres = [];
            foreach ($likedRecommendations as $recommendation) {
                $song = $this->songRepository->find($recommendation->song_id);
                if ($song && $song->genre) {
                    $likedGenres[] = $song->genre;
                }
            }

            // Count genre occurrences to find favorites
            $genreCounts = array_count_values($likedGenres);
            arsort($genreCounts);

            // Get top genres
            $topGenres = array_keys(array_slice($genreCounts, 0, 3));

            if (empty($topGenres)) {
                return $this->songRepository->all($limit)->getCollection();
            }

            // Get songs from those genres, excluding ones the user already rated
            $ratedSongIds = Recommendation::where('user_id', $user->id)
                ->pluck('song_id')
                ->toArray();

            // Using DB query to get songs by genres
            $songs = DB::table('songs')
                ->whereIn('genre', $topGenres)
                ->whereNotIn('id', $ratedSongIds)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            // Convert to Song collection
            $songIds = $songs->pluck('id')->toArray();
            return $this->songRepository->findMany($songIds);
        });
    }
}
