<?php

namespace App\Services\Song;

use App\Models\Song;
use App\Repositories\Interfaces\SongRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class SongService implements ISongService
{
    protected $songRepository;

    /**
     * SongService constructor.
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
     * @return LengthAwarePaginator
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
        $song = $this->songRepository->find($songId);

        if (!$song) {
            return collect([]);
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
        $songs = Song::whereIn('id', $songIds)->get();

        if ($songs->isEmpty()) {
            return collect([]);
        }

        return $this->songRepository->findSimilarToMultipleSongs($songs, $limit);
    }

    /**
     * Generate embedding for song data
     * Using OpenAI's API to generate embeddings
     *
     * @param array $songData
     * @return array
     */
    public function generateEmbedding(array $songData): array
    {
        // Combine song data into a single text for embedding
        $text = implode(' ', [
            $songData['title'] ?? '',
            $songData['artist'] ?? '',
            $songData['album'] ?? '',
            $songData['genre'] ?? '',
            (string)($songData['year'] ?? ''),
            $songData['lyrics'] ?? ''
        ]);

        // Call OpenAI API to generate embedding
        // Note: You should set OPENAI_API_KEY in your .env file
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-ada-002',
                'input' => $text
            ]);

        if ($response->successful()) {
            return $response->json()['data'][0]['embedding'];
        }

        // Return empty array if API call fails
        // In production, you might want to handle this error differently
        return array_fill(0, 8, 0);
    }

    /**
     * Store a new song with its embedding
     *
     * @param array $data
     * @return Song
     */
    public function storeSong(array $data): Song
    {
        // Generate embedding for the song
        $embedding = $this->generateEmbedding($data);

        // Convert embedding to JSON string for storage
        $data['embedding'] = json_encode($embedding);

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
        $song = $this->songRepository->find($id);

        if (!$song) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Song with ID {$id} not found");
        }

        // Check if any fields that would affect embedding are changed
        $embeddingFields = ['title', 'artist', 'album', 'genre', 'year', 'lyrics'];
        $needsNewEmbedding = false;

        foreach ($embeddingFields as $field) {
            if (isset($data[$field]) && $song->{$field} !== $data[$field]) {
                $needsNewEmbedding = true;
                break;
            }
        }

        // Generate new embedding if needed
        if ($needsNewEmbedding) {
            // Merge current song data with updates for embedding generation
            $songData = array_merge($song->toArray(), $data);
            $embedding = $this->generateEmbedding($songData);
            $data['embedding'] = json_encode($embedding);
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
        $song = $this->songRepository->find($id);
        return $song->delete();
    }
}
