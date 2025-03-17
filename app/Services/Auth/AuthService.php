<?php

namespace App\Services\Auth;

use App\Services\Auth\IAuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService implements IAuthService
{
    public function authenticateUser(LoginRequest $request): void
    {
        $request->authenticate();
        $request->session()->regenerate();
    }

    public function logoutUser(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function registerUser(array $userData): User
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $credentials, callable $callback): string
    {
        return Password::reset($credentials, $callback);
    }

    public function verifyEmail(Request $request): bool
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return true;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    public function sendVerificationNotification(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * Confirm a user's password.
     */
    public function confirmPassword(User $user, string $password): bool
    {
        return Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $password,
        ]);
    }
}
