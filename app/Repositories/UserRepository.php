<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements \App\Contracts\User
{
    public function updateName(User $user, string $name): bool
    {
        return $user->update(['name' => $name]);
    }

    public function updateEmail(User $user, string $newEmail): bool
    {
        return $user->update(['email' => $newEmail]);
    }

    public function updatePassword(User $user, string $newPassword): bool
    {
        return $user->update(['password' => $newPassword]);
    }
}
