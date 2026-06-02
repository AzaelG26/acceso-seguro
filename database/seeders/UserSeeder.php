<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de usuarios.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Guest1234!@'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('User1234!@'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'password' => Hash::make('Admin1234!@'),
            'role' => 'guest',
        ]);
    }
}
