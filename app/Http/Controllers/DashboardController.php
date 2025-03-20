<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard based on user role.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if ($request->user()->isAdmin()) {
            // Redirect admin users to the admin dashboard
            return redirect()->route('admin.songs.index');
        }

        // Regular users see the normal dashboard
        return Inertia::render('user/Dashboard');
    }

    /**
     * Display the personalized page for regular users.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function personalized(Request $request)
    {
        // Redirect admin users to their dashboard
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.songs.index');
        }

        return Inertia::render('user/Personalized');
    }
}
