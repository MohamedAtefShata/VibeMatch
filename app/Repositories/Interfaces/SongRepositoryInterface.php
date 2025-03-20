<?php

namespace App\Repositories\Interfaces;

use App\Models\Song;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SongRepositoryInterface
{
    /**
     * Get all songs
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get song by ID
     *
     * @param int $id
     * @return Song|null
     */
    public function find(int $id): ?Song;

    /**
     * Get multiple songs by their IDs
     *
     * @param array $ids
     * @return Collection
     */
    public function findMany(array $ids): Collection;

    /**
     * Create a new song
     *
     * @param array $data
     * @return Song
     */
    public function create(array $data): Song;

    /**
     * Update an existing song
     *
     * @param int $id
     * @param array $data
     * @return Song
     */
    public function update(int $id, array $data): Song;

    /**
     * Delete a song
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Search songs by title or artist
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function search(string $query, int $limit = 10): Collection;

    /**
     * Find similar songs based on vector similarity
     *
     * @param array $embedding
     * @param int $limit
     * @param array $excludeIds
     * @return Collection
     */
    public function findSimilar(array $embedding, int $limit = 5, array $excludeIds = []): Collection;

    /**
     * Find similar songs to a given song
     *
     * @param Song $song
     * @param int $limit
     * @return Collection
     */
    public function findSimilarToSong(Song $song, int $limit = 5): Collection;

    /**
     * Find similar songs to a list of songs by averaging their embeddings
     *
     * @param Collection $songs
     * @param int $limit
     * @return Collection
     */
    public function findSimilarToMultipleSongs(Collection $songs, int $limit = 5): Collection;
}
