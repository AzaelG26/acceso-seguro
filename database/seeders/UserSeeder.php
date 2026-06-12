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
            'name' => 'Aozmar Azael Garcia Candela',
            'email' => 'azagarcan05@gmail.com',
            'password' => Hash::make('Mdkcadmin198$'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Daniel García',
            'email' => 'garciaazamardaniel@gmail.com',
            'password' => Hash::make('Ludtzkdbipdnq3?'),
            'role' => 'user',
        ]);

    }
}
