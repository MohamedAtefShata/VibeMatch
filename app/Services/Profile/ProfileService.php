<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileService implements IprofileService
{
    public function updateProfile(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    public function deleteAccount(User $user): void
    {
        $user->delete();
    }
}
