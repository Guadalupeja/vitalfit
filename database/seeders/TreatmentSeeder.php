<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Treatment;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // FACIALES (Rosa)
            [
                'name' => 'Facial hidratante',
                'category' => 'faciales',
                'duration_minutes' => 90,
                'color_hex' => '#EC4899', // rosa
                'description' => 'Tratamiento facial (ejemplo base).',
                'active' => true,
            ],

            // APARATOLOGÍA (Morado)
            [
                'name' => 'Aparatología reductiva (zona)',
                'category' => 'aparatologia',
                'duration_minutes' => 120,
                'color_hex' => '#7C3AED', // morado
                'description' => 'Aparatología estética (ejemplo base).',
                'active' => true,
            ],

            // ESTÉTICOS (Amarillo)
            [
                'name' => 'Tratamiento estético (sesión)',
                'category' => 'esteticos',
                'duration_minutes' => 60,
                'color_hex' => '#F59E0B', // amarillo
                'description' => 'Tratamientos estéticos (ejemplo base).',
                'active' => true,
            ],

            // DEPILACIÓN LÁSER (Lavanda)
            [
                'name' => 'Depilación láser (zona)',
                'category' => 'laser',
                'duration_minutes' => 60,
                'color_hex' => '#C4B5FD', // lavanda
                'description' => 'Depilación láser (ejemplo base).',
                'active' => true,
            ],

            // NUTRICIÓN (Verde esmeralda)
            [
                'name' => 'Nutrición - Consulta inicial',
                'category' => 'nutricion',
                'duration_minutes' => 60,
                'color_hex' => '#059669', // esmeralda
                'description' => 'Consulta inicial de nutrición (ejemplo base).',
                'active' => true,
            ],
            [
                'name' => 'Nutrición - Seguimiento',
                'category' => 'nutricion',
                'duration_minutes' => 30,
                'color_hex' => '#059669', // esmeralda
                'description' => 'Seguimiento de nutrición (ejemplo base).',
                'active' => true,
            ],

            // VALORACIÓN INICIAL (Mandarina)
            [
                'name' => 'Valoración inicial',
                'category' => 'valoracion',
                'duration_minutes' => 60,
                'color_hex' => '#F97316', // mandarina
                'description' => 'Valoración inicial (ejemplo base).',
                'active' => true,
            ],
        ];

        foreach ($items as $data) {
            Treatment::updateOrCreate(
                ['name' => $data['name']], // clave para evitar duplicados
                $data
            );
        }
    }
}
