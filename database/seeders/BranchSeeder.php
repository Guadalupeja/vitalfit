<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['slug' => 'serdan'],
            ['name' => 'Serdán', 'active' => true]
        );

        Branch::updateOrCreate(
            ['slug' => 'zacapoaxtla'],
            ['name' => 'Zacapoaxtla', 'active' => true]
        );
    }
}