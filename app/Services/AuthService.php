<?php
namespace App\Services;

use App\Exceptions\InvalidCredentialException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        protected \App\Contracts\Auth $authRepository
    ) {}

    public function loginAsAdmin(string $email, string $password)
    {
        $admin = $this->authRepository->findAdminWithEmail($email);

        if (!Auth::attempt(['email' => $admin->email, 'password' => $password])) throw new InvalidCredentialException();

        $user = Auth::user();

        if ($user == null) throw new InvalidCredentialException();

        return $user->createToken('admin_token', config('ability.admin'))->plainTextToken;
    }

    public function loginAsStaff(string $email, string $password)
    {
        $staff = $this->authRepository->findStaffWithEmail($email);

        if (!Auth::attempt(['email' => $staff->email, 'password' => $password])) throw new InvalidCredentialException();

        $user = Auth::user();

        if ($user == null) throw new InvalidCredentialException();

        return $user->createToken('staff_token', config('ability.staff'))->plainTextToken;
    }

    public function logoutAnyRole(): bool
    {
        $user = Auth::user();
        
        if ($user == null) throw new InvalidCredentialException();

        return (bool) optional($user->currentAccessToken()->delete());
    }
}