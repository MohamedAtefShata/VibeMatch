<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\User\IUserService;
use App\Services\Auth\IAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * @var IUserService
     */
    protected $userService;

    /**
     * @var IAuthService
     */
    protected $authService;

    /**
     * Constructor with dependency injection
     *
     * @param IUserService $userService
     * @param IAuthService $authService
     */
    public function __construct(IUserService $userService, IAuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * Display the user management page.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $users = $this->userService->getAllUsers($perPage);

        // Add is_admin property to each user for the frontend
        $users->getCollection()->transform(function ($user) {
            $user->is_admin = $user->isAdmin();
            return $user;
        });

        return Inertia::render('admin/AdminUserManagement', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user using auth service for consistent registration logic
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        try {
            $user = $this->authService->registerUser($userData);

            // Set admin status if requested
            if ($request->has('is_admin') && $request->is_admin) {
                $this->userService->updateAdminStatus($user, true);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update user data
            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
            }

            // Update basic user data if we have any
            if (!empty($updateData)) {
                $this->userService->updateUser($user, $updateData);
            }

            // Handle admin status separately if provided
            if ($request->has('is_admin')) {
                $this->userService->updateAdminStatus($user, $request->is_admin);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                return redirect()->back()->with('error', 'User not found');
            }

            // Delete user
            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Update user role (admin status)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                return redirect()->back()->with('error', 'User not found');
            }

            $validator = Validator::make($request->all(), [
                'is_admin' => ['required', 'boolean'],
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update admin status
            $this->userService->updateAdminStatus($user, $request->is_admin);

            return redirect()->route('admin.users.index')
                ->with('success', 'User role updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user role: ' . $e->getMessage());
        }
    }
}
