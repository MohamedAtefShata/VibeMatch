<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Song\ISongService;
use App\Services\User\IUserService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * The song service instance.
     *
     * @var ISongService
     */
    protected $songService;

    /**
     * The user service instance.
     *
     * @var IUserService
     */
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @param ISongService $songService
     * @param IUserService $userService
     */
    public function __construct(ISongService $songService, IUserService $userService)
    {
        $this->songService = $songService;
        $this->userService = $userService;
    }

    /**
     * Display admin songs management dashboard.
     *
     * @return Response
     */
    public function songs(): Response
    {
        $perPage = 15;
        $songs = $this->songService->getAllSongs($perPage);

        return Inertia::render('admin/AdminDashboard', [
            'songs' => $songs
        ]);
    }

    /**
     * Display admin user management dashboard.
     *
     * @return Response
     */
    public function users(): Response
    {
        $perPage = 15;
        $users = $this->userService->getAllUsers($perPage);

        // Add is_admin property to each user
        $users->getCollection()->transform(function ($user) {
            $user->is_admin = $user->isAdmin();
            return $user;
        });
        return Inertia::render('admin/AdminUserManagement', [
                'users' => $users]);
    }
}
