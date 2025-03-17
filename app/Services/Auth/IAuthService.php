<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

interface IAuthService
{
    public function authenticateUser(LoginRequest $request): void;
    public function logoutUser(Request $request): void;
    public function registerUser(array $userData): User;
    public function sendPasswordResetLink(string $email): string;
    public function resetPassword(array $credentials, callable $callback): string;
    public function verifyEmail(Request $request): bool;
    public function sendVerificationNotification(User $user): void;
    public function confirmPassword(User $user, string $password): bool;
}
