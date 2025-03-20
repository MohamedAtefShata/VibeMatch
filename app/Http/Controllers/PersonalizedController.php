<?php

namespace App\Http\Controllers;

use App\Services\Recommendation\IRecommendationService;
use App\Services\Song\ISongService;
use App\Services\User\IUserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonalizedController extends Controller
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
     * @var IUserService
     */
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @param IRecommendationService $recommendationService
     * @param ISongService $songService
     * @param IUserService $userService
     */
    public function __construct(
        IRecommendationService $recommendationService,
        ISongService $songService,
        IUserService $userService
    ) {
        $this->recommendationService = $recommendationService;
        $this->songService = $songService;
        $this->userService = $userService;
    }

    /**
     * Show the personalized dashboard.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            $user = auth()->user();
            $limit = $request->input('limit', 5);
            $historyLimit = $request->input('history_limit', 10);

            // Get personalized recommendations
            $forYou = $this->recommendationService->getPersonalizedRecommendations($user, $limit);

            // Get genre-based recommendations
            $basedOnGenre = $this->recommendationService->getGenreBasedRecommendations($user, $limit);

            // Get new releases
            $newReleases = $this->songService->getNewestSongs($limit);

            // Get recommendation history
            $recommendationHistory = $this->recommendationService->getUserRecommendationHistory($user, $historyLimit);

            // Transform the history to include song details
            $transformedHistory = $recommendationHistory->map(function ($recommendation) {
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


            return Inertia::render('Personalized/Index', [
                'recommendations' => [
                    'forYou' => $forYou,
                    'basedOnGenre' => $basedOnGenre,
                    'newReleases' => $newReleases
                ],
                'recommendationHistory' => $transformedHistory,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personalized/Index', [
                'recommendations' => [
                    'forYou' => [],
                    'basedOnGenre' => [],
                    'newReleases' => []
                ],
                'recommendationHistory' => [],
                'profile' => [],
                'success' => false,
                'error' => 'Failed to load personalized recommendations: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Rate a recommendation.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function rateRecommendation(Request $request, int $id): Response
    {
        try {
            $rating = $request->input('rating');
            $success = $this->recommendationService->rateRecommendation($id, $rating);

            if (!$success) {
                return Inertia::render('Personalized/Rate', [
                    'success' => false,
                    'error' => 'Failed to rate recommendation'
                ]);
            }

            return Inertia::render('Personalized/Rate', [
                'success' => true,
                'message' => 'Recommendation rated successfully'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personalized/Rate', [
                'success' => false,
                'error' => 'Failed to rate recommendation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a new recommendation.
     *
     * @param Request $request
     * @return Response
     */
    public function storeRecommendation(Request $request): Response
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

            return Inertia::render('Personalized/Store', [
                'recommendation' => $recommendation,
                'success' => true,
                'message' => 'Recommendation stored successfully'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personalized/Store', [
                'success' => false,
                'error' => 'Failed to store recommendation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get recommendation history.
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

            return Inertia::render('Personalized/History', [
                'recommendations' => $transformedHistory,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personalized/History', [
                'recommendations' => [],
                'success' => false,
                'error' => 'Failed to retrieve recommendation history: ' . $e->getMessage()
            ]);
        }
    }
}
