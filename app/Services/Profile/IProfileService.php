<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\Request;

interface IProfileService
{
    public function updateProfile(User $user, array $data): void;
    public function updatePassword(User $user, string $newPassword): void;
    public function deleteAccount(User $user): void;
}
