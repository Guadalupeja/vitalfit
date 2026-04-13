<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;

class BranchUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@vitalfit.local')->first();
        $user = User::where('email', 'user@vitalfit.local')->first();

        $serdan = Branch::where('slug', 'serdan')->first();
        $zacapoaxtla = Branch::where('slug', 'zacapoaxtla')->first();

        if ($admin && $serdan && $zacapoaxtla) {
            $admin->branches()->syncWithoutDetaching([$serdan->id, $zacapoaxtla->id]);
        }

        if ($user && $serdan) {
            $user->branches()->syncWithoutDetaching([$serdan->id]);
        }
    }
}