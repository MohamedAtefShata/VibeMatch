<?php

namespace App\Services\Song;

use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;

interface ISongService
{
    /**
     * Get all songs with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllSongs(int $perPage = 15);

    /**
     * Get a song by ID
     *
     * @param int $id
     * @return Song|null
     */
    public function getSongById(int $id): ?Song;

    /**
     * Search songs by title or artist
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function searchSongs(string $query, int $limit = 10): Collection;

    /**
     * Get recommendations based on a single song
     *
     * @param int $songId
     * @param int $limit
     * @return Collection
     */
    public function getRecommendationsForSong(int $songId, int $limit = 5): Collection;

    /**
     * Get recommendations based on multiple songs
     *
     * @param array $songIds
     * @param int $limit
     * @return Collection
     */
    public function getRecommendationsForMultipleSongs(array $songIds, int $limit = 5): Collection;

    /**
     * Generate embedding for song data
     *
     * @param array $songData
     * @return array
     */
    public function generateEmbedding(array $songData): array;

    /**
     * Store a new song with its embedding
     *
     * @param array $data
     * @return Song
     */
    public function storeSong(array $data): Song;

    /**
     * Update a song and its embedding if necessary
     *
     * @param int $id
     * @param array $data
     * @return Song
     */
    public function updateSong(int $id, array $data): Song;


    /**
     * Delete a song by its ID.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteSong(int $id): bool;
}
