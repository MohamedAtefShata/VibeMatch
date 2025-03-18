<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\User\IUserService;
use App\Services\Auth\IAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

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
     * Display a listing of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get paginated users from the service
        $perPage = $request->get('per_page', 15);
        $users = $this->userService->getAllUsers($perPage);

        // Add is_admin property to each user for the frontend
        $users->getCollection()->transform(function ($user) {
            $user->is_admin = $user->isAdmin();
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create user using auth service for consistent registration logic
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'status' => $request->status ?? 'active',
        ];

        $user = $this->authService->registerUser($userData);

        // Set admin status if requested
        if ($request->has('is_admin') && $request->is_admin) {
            $this->userService->updateAdminStatus($user, true);
        }

        return response()->json($user, 201);
    }

    /**
     * Update the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        // Update basic user data if we have any
        if (!empty($updateData)) {
            $this->userService->updateUser($user, $updateData);
        }

        // Handle admin status separately if provided
        if ($request->has('is_admin')) {
            $this->userService->updateAdminStatus($user, $request->is_admin);
        }

        // Get fresh user data
        $updatedUser = $this->userService->getUserById($id);
        $updatedUser->is_admin = $updatedUser->isAdmin();

        return response()->json($updatedUser);
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete user
        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Update user status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update status
        $this->userService->updateUserStatus($user, $request->status);

        return response()->json(['message' => 'User status updated successfully']);
    }

    /**
     * Update user role (admin status)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRole(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'is_admin' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update admin status
        $this->userService->updateAdminStatus($user, $request->is_admin);

        return response()->json(['message' => 'User role updated successfully']);
    }
}
