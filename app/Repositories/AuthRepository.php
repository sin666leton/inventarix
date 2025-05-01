<?php
namespace App\Repositories;

use App\Exceptions\InvalidCredentialException;
use App\Models\User;

class AuthRepository implements \App\Contracts\Auth
{
    public function findAdminWithEmail(string $email): User
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })
        ->where('email', $email)
        ->firstOr(function () {
            throw new InvalidCredentialException();
        });

        return $user;
    }

    public function findStaffWithEmail(string $email): User
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('name', 'staff');
        })
        ->where('email', $email)
        ->firstOr(function () {
            throw new InvalidCredentialException();
        });

        return $user;
    }
}
