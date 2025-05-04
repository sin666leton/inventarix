<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialException;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public function __construct(
        protected \App\Contracts\User $userRepository,
        protected Hasher $hash,
        protected LogManager $logger
    ){}

    public function updateName(User $user, string $name)
    {
        $result = $this->userRepository->updateName($user, $name);
    
        if ($result) {
            $key = $user->role->name == "admin" ? "admin_": "staff_";

            Cache::forget($key.$user->id);
            $this->logger->channel('model')->info('Update user name.', [
                'id' => $user->id,
                'role' => $user->role->name,
                'name' => $name
            ]);
        }

        return $result;
    }

    public function updateEmail(User $user, string $newEmail, string $password)
    {
        if (!$this->hash->check($password, $user->password)) throw new InvalidCredentialException("Wrong password.");
        
        $result = $this->userRepository->updateEmail($user, $newEmail);

        if ($result) {
            $key = $user->role->name == "admin" ? "admin_": "staff_";

            Cache::forget($key.$user->id);
            $this->logger->channel('model')->info('Update user email.', [
                'id' => $user->id,
                'role' => $user->role->name,
                'email' => $newEmail
            ]);
        }

        return $result;
    }

    public function updatePassword(User $user, string $oldPassword, string $newPassword)
    {
        if (!$this->hash->check($oldPassword, $user->password)) throw new InvalidCredentialException("Wrong password.");
    
        $result = $this->userRepository->updatePassword($user, $this->hash->make($newPassword));

        if ($result) {
            $key = $user->role->name == "admin" ? "admin_": "staff_";

            Cache::forget($key.$user->id);
            $this->logger->channel('model')->info('Update user password.', [
                'id' => $user->id,
                'role' => $user->role->name,
            ]);
        }

        return $result;
    }
}
