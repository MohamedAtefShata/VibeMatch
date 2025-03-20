<?php

namespace App\Http\Controllers\Recommendation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recommendation\RateRecommendationRequest;
use App\Http\Requests\Recommendation\StoreRecommendationRequest;
use App\Models\Recommendation;
use App\Services\Recommendation\IRecommendationService;
use App\Services\Song\ISongService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    /**
     * @var IRecommendationService
     */
    protected $recommendationService;

    /**
     * @var ISongService
     */
    protected $songService;

    /**
     * Create a new controller instance.
     *
     * @param IRecommendationService $recommendationService
     * @param ISongService $songService
     */
    public function __construct(
        IRecommendationService $recommendationService,
        ISongService $songService
    ) {
        $this->recommendationService = $recommendationService;
        $this->songService = $songService;
    }

    /**
     * Store a new recommendation.
     *
     * @param StoreRecommendationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRecommendationRequest $request)
    {
        try {
            $userId = auth()->id();
            $songId = $request->input('song_id');
            $sourceSongIds = $request->input('source_song_ids', []);

            $recommendation = $this->recommendationService->storeRecommendation(
                $userId,
                $songId,
                $sourceSongIds
            );

            // Return JSON response with the stored recommendation
            return response()->json([
                'recommendation' => $recommendation,
                'message' => 'Recommendation stored successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to store recommendation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rate a recommendation.
     *
     * @param RateRecommendationRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function rate(RateRecommendationRequest $request, int $id)
    {
        try {
            $rating = $request->input('rating');
            $success = $this->recommendationService->rateRecommendation($id, $rating);

            if (!$success) {
                return response()->json([
                    'error' => 'Failed to rate recommendation'
                ], 400);
            }

            return response()->json([
                'message' => 'Recommendation rated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to rate recommendation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user recommendation history.
     *
     * @param Request $request
     * @return Response
     */
    public function history(Request $request)
    {
        try {
            $user = auth()->user();
            $limit = $request->input('limit', 10);

            $history = $this->recommendationService->getUserRecommendationHistory($user, $limit);

            // Transform the history to include song details
            $transformedHistory = $history->map(function ($recommendation) {
                $song = $this->songService->getSongById($recommendation->song_id);
                $sourceSongs = [];

                // Get source songs details
                foreach ($recommendation->source_song_ids as $sourceId) {
                    $sourceSong = $this->songService->getSongById($sourceId);
                    if ($sourceSong) {
                        $sourceSongs[] = [
                            'id' => $sourceSong->id,
                            'title' => $sourceSong->title,
                            'artist' => $sourceSong->artist,
                            'image_url' => $sourceSong->image_url,
                        ];
                    }
                }

                return [
                    'id' => $recommendation->id,
                    'recommendedSong' => $song,
                    'basedOn' => $sourceSongs,
                    'timestamp' => $recommendation->created_at,
                    'liked' => $recommendation->rating, // null, 1 (like), or -1 (dislike)
                ];
            });

            // Return recommendations data with a 200 status
            return response()->json($transformedHistory);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve recommendation history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get personalized recommendations.
     *
     * @param Request $request
     * @return Response
     */
    public function personalized(Request $request)
    {
        try {
            $user = auth()->user();
            $limit = $request->input('limit', 5);

            // Get recommendations based on what the user has liked
            $forYou = $this->recommendationService->getPersonalizedRecommendations($user, $limit);

            // Get recommendations based on genres the user likes
            $basedOnGenre = $this->recommendationService->getGenreBasedRecommendations($user, $limit);

            // Get newest songs (as a placeholder for new releases)
            $newReleases = $this->songService->getNewestSongs($limit);

            $recommendationsData = [
                'forYou' => $forYou,
                'basedOnGenre' => $basedOnGenre,
                'newReleases' => $newReleases
            ];

            // For Inertia, we use the existing page and render to it
            return Inertia::render('Personalized', [
                'recommendations' => $recommendationsData,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personalized', [
                'success' => false,
                'error' => 'Failed to get personalized recommendations: ' . $e->getMessage()
            ]);
        }
    }
}
