<?php

namespace App\Repositories;

use App\Models\Song;
use App\Repositories\Interfaces\SongRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SongRepository implements SongRepositoryInterface
{
    /**
     * Get all songs
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return Song::paginate($perPage);
    }

    /**
     * Get song by ID
     *
     * @param int $id
     * @return Song|null
     */
    public function find(int $id): ?Song
    {
        return Song::find($id);
    }

    /**
     * Get multiple songs by their IDs
     *
     * @param array $ids
     * @return Collection
     */
    public function findMany(array $ids): Collection
    {
        return Song::whereIn('id', $ids)->get();
    }

    /**
     * Create a new song
     *
     * @param array $data
     * @return Song
     */
    public function create(array $data): Song
    {
        return Song::create($data);
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
        $song = $this->find($id);
        if (!$song) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Song with ID {$id} not found");
        }

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
        return Song::where('title', 'LIKE', "%{$query}%")
            ->orWhere('artist', 'LIKE', "%{$query}%")
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
        // In a real application, this would use a vector database or PostgreSQL's vector similarity functions
        // For simplicity in this example, we'll just return random songs
        $query = Song::query();

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->inRandomOrder()->limit($limit)->get();
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
        // Get songs with the same genre or by the same artist
        $similarSongs = Song::where('id', '!=', $song->id)
            ->where(function ($query) use ($song) {
                $query->where('genre', $song->genre)
                    ->orWhere('artist', $song->artist);
            })
            ->limit($limit)
            ->get();

        // If not enough songs found, fill with random songs
        if ($similarSongs->count() < $limit) {
            $existingIds = $similarSongs->pluck('id')->push($song->id)->toArray();
            $remainingCount = $limit - $similarSongs->count();

            $additionalSongs = Song::whereNotIn('id', $existingIds)
                ->inRandomOrder()
                ->limit($remainingCount)
                ->get();

            $similarSongs = $similarSongs->merge($additionalSongs);
        }

        return $similarSongs;
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
        // Get unique genres and artists from the songs
        $genres = $songs->pluck('genre')->unique()->filter()->toArray();
        $artists = $songs->pluck('artist')->unique()->filter()->toArray();
        $excludeIds = $songs->pluck('id')->toArray();

        // Find songs with matching genres or artists
        $query = Song::whereNotIn('id', $excludeIds);

        if (!empty($genres) || !empty($artists)) {
            $query->where(function($q) use ($genres, $artists) {
                if (!empty($genres)) {
                    $q->whereIn('genre', $genres);
                }

                if (!empty($artists)) {
                    if (!empty($genres)) {
                        $q->orWhereIn('artist', $artists);
                    } else {
                        $q->whereIn('artist', $artists);
                    }
                }
            });
        }

        $similarSongs = $query->limit($limit)->get();

        // If not enough songs found, fill with random songs
        if ($similarSongs->count() < $limit) {
            $existingIds = $similarSongs->pluck('id')->merge($excludeIds)->toArray();
            $remainingCount = $limit - $similarSongs->count();

            $additionalSongs = Song::whereNotIn('id', $existingIds)
                ->inRandomOrder()
                ->limit($remainingCount)
                ->get();

            $similarSongs = $similarSongs->merge($additionalSongs);
        }

        return $similarSongs;
    }
}
