<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    /**
     * Get the user's profile information.
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // Build a basic profile response
        // In a real implementation, you'd get this data from a proper user service
        $profileData = [
            'favoriteGenres' => [], // This would come from user's listening history
            'recentPlays' => [],   // This would come from a plays/history table
            'topArtists' => []     // This would be derived from listening history
        ];

        if ($request->wantsJson()) {
            return response()->json($profileData);
        }

        return Inertia::render('User/Profile', [
            'profile' => $profileData
        ]);
    }
}
