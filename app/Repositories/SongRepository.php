<?php

namespace App\Repositories;

use App\Models\Song;
use App\Repositories\Interfaces\SongRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SongRepository implements SongRepositoryInterface
{
    protected $model;

    /**
     * SongRepository constructor.
     *
     * @param Song $song
     */
    public function __construct(Song $song)
    {
        $this->model = $song;
    }

    /**
     * Get all songs with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('title')->paginate($perPage);
    }

    /**
     * Get song by ID
     *
     * @param int $id
     * @return Song|null
     */
    public function find(int $id): ?Song
    {
        return $this->model->find($id);
    }

    /**
     * Create a new song
     *
     * @param array $data
     * @return Song
     */
    public function create(array $data): Song
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing song
     *
     * @param int $id
     * @param array $data
     * @return Song
     */
    public function update(int $id, array $data): Song
    {
        $song = $this->model->findOrFail($id);
        $song->update($data);
        return $song->fresh();
    }

    /**
     * Delete a song
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $song = $this->find($id);

        if (!$song) {
            return false;
        }

        return $song->delete();
    }

    /**
     * Search songs by title or artist
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function search(string $query, int $limit = 10): Collection
    {
        return $this->model->where('title', 'ilike', "%{$query}%")
            ->orWhere('artist', 'ilike', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Find similar songs based on vector similarity
     *
     * @param array $embedding
     * @param int $limit
     * @param array $excludeIds
     * @return Collection
     */
    public function findSimilar(array $embedding, int $limit = 5, array $excludeIds = []): Collection
    {
        // Convert PHP array to PostgreSQL vector format
        $vectorString = '[' . implode(',', $embedding) . ']';

        $query = $this->model->select('*')
            ->selectRaw("1 - (embedding <=> '$vectorString'::vector) as similarity")
            ->whereNotIn('id', $excludeIds)
            ->orderByRaw("embedding <=> '$vectorString'::vector")
            ->limit($limit);

        return $query->get();
    }

    /**
     * Find similar songs to a given song
     *
     * @param Song $song
     * @param int $limit
     * @return Collection
     */
    public function findSimilarToSong(Song $song, int $limit = 5): Collection
    {
        return $this->findSimilar(
            json_decode($song->embedding),
            $limit,
            [$song->id]
        );
    }

    /**
     * Find similar songs to a list of songs by averaging their embeddings
     *
     * @param Collection $songs
     * @param int $limit
     * @return Collection
     */
    public function findSimilarToMultipleSongs(Collection $songs, int $limit = 5): Collection
    {
        if ($songs->isEmpty()) {
            return collect([]);
        }

        // Extract embeddings from songs
        $embeddings = $songs->map(function ($song) {
            return json_decode($song->embedding);
        });

        // Average the embeddings
        $avgEmbedding = [];
        $dimensions = count($embeddings[0]);

        for ($i = 0; $i < $dimensions; $i++) {
            $sum = 0;
            foreach ($embeddings as $embedding) {
                $sum += $embedding[$i];
            }
            $avgEmbedding[$i] = $sum / count($embeddings);
        }

        return $this->findSimilar(
            $avgEmbedding,
            $limit,
            $songs->pluck('id')->toArray()
        );
    }
}
