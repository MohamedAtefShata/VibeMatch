<?php

namespace App\Http\Controllers\Song;

use App\Http\Controllers\Controller;
use App\Http\Requests\Song\RecommendationRequest;
use App\Http\Requests\Song\SearchSongRequest;
use App\Http\Requests\Song\StoreSongRequest;
use App\Http\Requests\Song\UpdateSongRequest;
use App\Services\Song\ISongService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SongController extends Controller
{
    /**
     * The song service instance.
     *
     * @var ISongService
     */
    protected $songService;

    /**
     * Create a new controller instance.
     *
     * @param ISongService $songService
     */
    public function __construct(ISongService $songService)
    {
        $this->songService = $songService;
    }

    /**
     * Display the song library page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 15);
        $songs = $this->songService->getAllSongs($perPage);

        return Inertia::render('Songs/Index', [
            'songs' => $songs
        ]);
    }

    /**
     * Display the song recommendation page.
     *
     * @return Response
     */
    public function recommendationPage(): Response
    {
        return Inertia::render('user/Dashboard');
    }

    /**
     * Search for songs.
     *
     * @param SearchSongRequest $request
     * @return Response
     */
    public function search(SearchSongRequest $request): Response
    {
        $query = $request->input('query');
        $limit = $request->input('limit', 10);

        try {
            $songs = $this->songService->searchSongs($query, $limit);
            return Inertia::render('user/Dashboard', [
                'results' => $songs,
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return Inertia::render('user/Dashboard', [
                'results' => [],
                'query' => $query,
                'error' => 'Failed to search songs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get recommendations based on selected songs.
     *
     * @param RecommendationRequest $request
     * @return Response
     */
    public function recommend(RecommendationRequest $request): Response
    {
        $songIds = $request->input('songIds');
        $limit = $request->input('limit', 5);

        try {
            $recommendations = $this->songService->getRecommendationsForMultipleSongs($songIds, $limit);
            return Inertia::render('user/Dashboard', [
                'recommendation' => $recommendations,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return Inertia::render('user/Dashboard', [
                'recommendation' => [],
                'success' => false,
                'error' => 'Failed to generate recommendations: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show a single song.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id): Response
    {
        try {
            $song = $this->songService->getSongById($id);

            if (!$song) {
                return Inertia::render('Error', [
                    'status' => 404,
                    'message' => 'Song not found'
                ]);
            }

            $similar = $this->songService->getRecommendationsForSong($id);

            return Inertia::render('user/Dashboard', [
                'song' => $song,
                'similar' => $similar
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Error', [
                'status' => 500,
                'message' => 'Failed to retrieve song data'
            ]);
        }
    }

    /**
     * Get previous recommendations for the user.
     *
     * @return Response
     */
    public function previousRecommendations(): Response
    {
        // Implementation would go here
        return Inertia::render('user/Dashboard', [
            'recommendations' => []
        ]);
    }

    /**
     * Store a new song.
     *
     * @param StoreSongRequest $request
     * @return Response
     */
    public function store(StoreSongRequest $request): Response
    {
        try {
            $song = $this->songService->storeSong($request->validated());

            return Inertia::render('Songs/Store', [
                'song' => $song,
                'success' => true,
                'message' => 'Song successfully stored'
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Songs/Store', [
                'success' => false,
                'error' => 'Failed to store song: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing song.
     *
     * @param UpdateSongRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateSongRequest $request, int $id): Response
    {
        try {
            $song = $this->songService->updateSong($id, $request->validated());

            return Inertia::render('Songs/Update', [
                'song' => $song,
                'success' => true,
                'message' => 'Song updated successfully'
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                ? 'Song not found'
                : 'Failed to update song: ' . $e->getMessage();

            $statusCode = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500;

            return Inertia::render('Songs/Update', [
                'success' => false,
                'error' => $errorMessage,
                'status' => $statusCode
            ]);
        }
    }

    /**
     * Delete a song.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        try {
            $song = $this->songService->getSongById($id);

            if (!$song) {
                return Inertia::render('Songs/Delete', [
                    'success' => false,
                    'error' => 'Song not found',
                    'status' => 404
                ]);
            }

            $this->songService->deleteSong($id);

            return Inertia::render('Songs/Delete', [
                'success' => true,
                'message' => 'Song deleted successfully'
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Songs/Delete', [
                'success' => false,
                'error' => 'Failed to delete song: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }
}
