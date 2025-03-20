<?php

namespace App\Services\Song;

use App\Models\Song;
use App\Repositories\Interfaces\SongRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SongService implements ISongService
{
    /**
     * @var SongRepositoryInterface
     */
    protected $songRepository;

    /**
     * Create a new service instance.
     *
     * @param SongRepositoryInterface $songRepository
     */
    public function __construct(SongRepositoryInterface $songRepository)
    {
        $this->songRepository = $songRepository;
    }

    /**
     * Get all songs with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllSongs(int $perPage = 15)
    {
        return $this->songRepository->all($perPage);
    }

    /**
     * Get a song by ID
     *
     * @param int $id
     * @return Song|null
     */
    public function getSongById(int $id): ?Song
    {
        return $this->songRepository->find($id);
    }

    /**
     * Search songs by title or artist
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function searchSongs(string $query, int $limit = 10): Collection
    {
        return $this->songRepository->search($query, $limit);
    }

    /**
     * Get recommendations based on a single song
     *
     * @param int $songId
     * @param int $limit
     * @return Collection
     */
    public function getRecommendationsForSong(int $songId, int $limit = 5): Collection
    {
        $song = $this->getSongById($songId);

        if (!$song) {
            Log::warning("Tried to get recommendations for non-existent song ID: {$songId}");
            return new Collection();
        }

        return $this->songRepository->findSimilarToSong($song, $limit);
    }

    /**
     * Get recommendations based on multiple songs
     *
     * @param array $songIds
     * @param int $limit
     * @return Collection
     */
    public function getRecommendationsForMultipleSongs(array $songIds, int $limit = 5): Collection
    {
        // Get the songs from the repository
        $songs = new Collection();
        foreach ($songIds as $id) {
            $song = $this->getSongById($id);
            if ($song) {
                $songs->push($song);
            }
        }

        if ($songs->isEmpty()) {
            Log::warning("No valid songs found for IDs: " . implode(', ', $songIds));
            return new Collection();
        }

        return $this->songRepository->findSimilarToMultipleSongs($songs, $limit);
    }

    /**
     * Generate embedding for song data
     *
     * @param array $songData
     * @return array
     */
    public function generateEmbedding(array $songData): array
    {
        // In a real application, this would use a text embedding model
        // For this example, we'll return a simple placeholder array
        return [0.1, 0.2, 0.3, 0.4, 0.5]; // Placeholder embedding
    }

    /**
     * Store a new song with its embedding
     *
     * @param array $data
     * @return Song
     */
    public function storeSong(array $data): Song
    {
        // Generate embedding if not provided
        if (!isset($data['embedding'])) {
            $data['embedding'] = json_encode($this->generateEmbedding($data));
        }

        return $this->songRepository->create($data);
    }

    /**
     * Update a song and its embedding if necessary
     *
     * @param int $id
     * @param array $data
     * @return Song
     */
    public function updateSong(int $id, array $data): Song
    {
        // Check if we need to regenerate the embedding
        $embeddingFields = ['title', 'artist', 'album', 'genre', 'lyrics'];
        $needsNewEmbedding = false;

        foreach ($embeddingFields as $field) {
            if (isset($data[$field])) {
                $needsNewEmbedding = true;
                break;
            }
        }

        if ($needsNewEmbedding) {
            // Get current song data
            $song = $this->getSongById($id);
            if (!$song) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Song with ID {$id} not found");
            }

            // Merge current data with new data
            $mergedData = array_merge([
                'title' => $song->title,
                'artist' => $song->artist,
                'album' => $song->album,
                'genre' => $song->genre,
                'lyrics' => $song->lyrics,
            ], $data);

            // Generate new embedding
            $data['embedding'] = json_encode($this->generateEmbedding($mergedData));
        }

        return $this->songRepository->update($id, $data);
    }

    /**
     * Delete a song by its ID.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteSong(int $id): bool
    {
        return $this->songRepository->delete($id);
    }

    /**
     * Get newest songs
     *
     * @param int $limit
     * @return Collection
     */
    public function getNewestSongs(int $limit = 5): Collection
    {
        // In a real application, this would fetch songs ordered by creation date
        // For this example, we'll just get random songs
        return Song::inRandomOrder()->limit($limit)->get();
    }
}
