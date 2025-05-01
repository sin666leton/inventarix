<?php

namespace App\Contracts;

interface User
{
    /**
     * Perbarui nama
     * 
     * @param string $name
     * @return bool
     */
    public function updateName(\App\Models\User $user, string $name): bool;

    /**
     * Perbarui email
     * 
     * @param string $newEmail
     * @return bool
     */
    public function updateEmail(\App\Models\User $user, string $newEmail): bool;

    /**
     * Perbarui password
     * 
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(\App\Models\User $user, string $newPassword): bool;
}
