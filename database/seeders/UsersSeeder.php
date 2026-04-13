<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (Nutrióloga)
        User::updateOrCreate(
            ['email' => 'admin@vitalfit.local'],
            [
                'name' => 'Admin VitalFit',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
            ]
        );

        // Usuario (Especialista/Recepción)
        User::updateOrCreate(
            ['email' => 'user@vitalfit.local'],
            [
                'name' => 'Usuario VitalFit',
                'password' => Hash::make('Password123!'),
                'role' => 'user',
            ]
        );
    }
}
