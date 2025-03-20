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
     * @return Response
     */
    public function store(StoreRecommendationRequest $request): Response
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

            return Inertia::render('user/Dashboard', [
                'recommendation' => $recommendation,
                'success' => true,
                'message' => 'Recommendation stored successfully'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('user/Dashboard', [
                'success' => false,
                'error' => 'Failed to store recommendation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Rate a recommendation.
     *
     * @param RateRecommendationRequest $request
     * @param int $id
     * @return Response
     */
    public function rate(RateRecommendationRequest $request, int $id): Response
    {
        try {
            $rating = $request->input('rating');
            $success = $this->recommendationService->rateRecommendation($id, $rating);

            if (!$success) {
                return Inertia::render('user/Dashboard', [
                    'success' => false,
                    'error' => 'Failed to rate recommendation'
                ]);
            }

            return Inertia::render('user/Dashboard', [
                'success' => true,
                'message' => 'Recommendation rated successfully'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('user/Dashboard', [
                'success' => false,
                'error' => 'Failed to rate recommendation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user recommendation history.
     *
     * @param Request $request
     * @return Response
     */
    public function history(Request $request): Response
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

            return Inertia::render('user/Dashboard', [
                'recommendations' => $transformedHistory,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return Inertia::render('user/Dashboard', [
                'recommendations' => [],
                'success' => false,
                'error' => 'Failed to retrieve recommendation history: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get personalized recommendations.
     *
     * @param Request $request
     * @return Response
     */
    public function personalized(Request $request): Response
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
