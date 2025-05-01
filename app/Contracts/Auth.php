<?php

namespace App\Contracts;

interface Auth
{
    /**
     * Cari user admin dengan email
     * 
     * @param string $email
     * @return \App\Models\User
     */
    public function findAdminWithEmail(string $email): \App\Models\User;

    /**
     * Cari user staff dengan email
     * 
     * @param string $email
     * @return \App\Models\User
     */
    public function findStaffWithEmail(string $email): \App\Models\User;
}
