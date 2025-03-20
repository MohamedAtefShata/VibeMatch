<?php

namespace App\Http\Controllers\Song;

use App\Http\Controllers\Controller;
use App\Services\Song\ISongService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
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
     * Get personalized recommendations for the authenticated user.
     *
     * @param Request $request
     * @return Response
     */
    public function personalizedRecommendations(Request $request): Response
    {
        $userId = auth()->id();
        $limit = $request->input('limit', 5);

        try {
            // For demo purposes, we'll use a hardcoded song ID to get recommendations
            // In a real implementation, this would be based on user's history
            $forYou = $this->songService->getRecommendationsForSong(1, $limit)->toArray();

            // Get recommendations based on multiple songs (user's favorites)
            // This is just a placeholder - in a real app, you'd get the user's favorite songs
            $userFavoriteSongs = [1, 2, 3];
            $basedOnGenre = $this->songService->getRecommendationsForMultipleSongs($userFavoriteSongs, $limit)->toArray();

            return Inertia::render('Songs/PersonalizedRecommendations', [
                'recommendations' => [
                    'forYou' => $forYou,
                    'basedOnGenre' => $basedOnGenre,
                    'newReleases' => [] // Placeholder for new releases
                ],
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get personalized recommendations: ' . $e->getMessage());

            return Inertia::render('Songs/PersonalizedRecommendations', [
                'recommendations' => [
                    'forYou' => [],
                    'basedOnGenre' => [],
                    'newReleases' => []
                ],
                'success' => false,
                'error' => 'Failed to get personalized recommendations: ' . $e->getMessage()
            ]);
        }
    }
}
