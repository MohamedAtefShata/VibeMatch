<?php

namespace App\Http\Controllers\Song;

use App\Http\Controllers\Controller;
use App\Http\Requests\Song\RecommendationRequest;
use App\Http\Requests\Song\SearchSongRequest;
use App\Http\Requests\Song\StoreSongRequest;
use App\Http\Requests\Song\UpdateSongRequest;
use App\Services\Song\ISongService;
use Illuminate\Http\JsonResponse;
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
        return Inertia::render('Songs/Recommendations');
    }

    /**
     * Search for songs.
     *
     * @param SearchSongRequest $request
     * @return JsonResponse
     */
    public function search(SearchSongRequest $request): JsonResponse
    {
        $query = $request->input('query');
        $limit = $request->input('limit', 10);

        try {
            $songs = $this->songService->searchSongs($query, $limit);
            return response()->json($songs);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to search songs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommendations based on selected songs.
     *
     * @param RecommendationRequest $request
     * @return JsonResponse
     */
    public function recommend(RecommendationRequest $request): JsonResponse
    {
        $songIds = $request->input('songIds');
        $limit = $request->input('limit', 5);

        try {
            $recommendations = $this->songService->getRecommendationsForMultipleSongs($songIds, $limit);
            return response()->json($recommendations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate recommendations: ' . $e->getMessage()
            ], 500);
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
                abort(404, 'Song not found');
            }

            $similar = $this->songService->getRecommendationsForSong($id);

            return Inertia::render('Song', [
                'song' => $song,
                'similar' => $similar
            ]);
        } catch (\Exception $e) {
            abort(500, 'Failed to retrieve song data');
        }
    }

    /**
     * Get previous recommendations for the user.
     *
     * @return JsonResponse
     */
    public function previousRecommendations(): JsonResponse
    {
        // Implementation would go here
        return response()->json([]);
    }

    /**
     * Store a new song.
     *
     * @param StoreSongRequest $request
     * @return JsonResponse
     */
    public function store(StoreSongRequest $request): JsonResponse
    {
        try {
            $song = $this->songService->storeSong($request->validated());

            return response()->json([
                'song' => $song,
                'message' => 'Song successfully stored'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to store song: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing song.
     *
     * @param UpdateSongRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSongRequest $request, int $id): JsonResponse
    {
        try {

            $song = $this->songService->updateSong($id, $request->validated());

            return response()->json([
                'song' => $song,
                'message' => 'Song updated successfully'
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                ? 'Song not found'
                : 'Failed to update song: ' . $e->getMessage();

            $statusCode = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500;

            return response()->json([
                'error' => $errorMessage
            ], $statusCode);
        }
    }

    /**
     * Delete a song.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $song = $this->songService->getSongById($id);

            if (!$song) {
                return response()->json([
                    'error' => 'Song not found'
                ], 404);
            }

            $this->songService->deleteSong($id);

            return response()->json([
                'message' => 'Song deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete song: ' . $e->getMessage()
            ], 500);
        }
    }
}
