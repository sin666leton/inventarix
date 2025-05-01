<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::insert([
            [
                'name' => 'admin'
            ],
            [
                'name' => 'staff'
            ]
        ]);

        User::create([
            'role_id' => 1,
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => Hash::make('123')
        ]);

        User::create([
            'role_id' => 2,
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('123')
        ]);
    }
}
