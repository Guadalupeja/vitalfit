<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\TreatmentType;
use Illuminate\Database\Seeder;

class TreatmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Faciales',
                'color_hex' => '#EC4899',
                'active' => true,
            ],
            [
                'name' => 'Aparatología',
                'color_hex' => '#7C3AED',
                'active' => true,
            ],
            [
                'name' => 'Tratamientos estéticos',
                'color_hex' => '#F59E0B',
                'active' => true,
            ],
            [
                'name' => 'Depilación láser',
                'color_hex' => '#C4B5FD',
                'active' => true,
            ],
            [
                'name' => 'Nutrición',
                'color_hex' => '#059669',
                'active' => true,
            ],
            [
                'name' => 'Valoración inicial',
                'color_hex' => '#F97316',
                'active' => true,
            ],
        ];

        $branches = Branch::query()->get(['id']);

        foreach ($branches as $branch) {
            foreach ($types as $type) {
                TreatmentType::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $type['name'],
                    ],
                    [
                        'color_hex' => strtoupper($type['color_hex']),
                        'active' => $type['active'],
                    ]
                );
            }
        }
    }
}