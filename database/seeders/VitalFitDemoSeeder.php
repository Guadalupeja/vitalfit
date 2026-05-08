<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\PackageTemplate;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Treatment;
use App\Models\TreatmentType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VitalFitDemoSeeder extends Seeder
{
    public function run(): void
    {
$branch = Branch::where('slug', 'serdan')->firstOrFail();

        $admin = User::firstOrCreate(
            ['email' => 'admin@vitalfit.test'],
            [
                'name' => 'Marisol Moreno',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $admin->branches()->syncWithoutDetaching([$branch->id]);

        $specialists = collect([
            ['name' => 'Especialista Nutrición', 'email' => 'nutricion@vitalfit.test'],
            ['name' => 'Especialista Aparatología', 'email' => 'aparato@vitalfit.test'],
            ['name' => 'Recepción VitalFit', 'email' => 'recepcion@vitalfit.test'],
        ])->map(function ($data) use ($branch) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'specialist',
                ]
            );

            $user->branches()->syncWithoutDetaching([$branch->id]);

            return $user;
        });

        $types = collect([
            ['name' => 'Faciales', 'color_hex' => '#F9A8D4'],
            ['name' => 'Aparatología estética', 'color_hex' => '#A855F7'],
            ['name' => 'Tratamientos estéticos', 'color_hex' => '#FACC15'],
            ['name' => 'Depilación láser', 'color_hex' => '#C4B5FD'],
            ['name' => 'Nutrición', 'color_hex' => '#10B981'],
            ['name' => 'Valoración inicial', 'color_hex' => '#FB923C'],
        ])->mapWithKeys(function ($data) use ($branch) {
            $type = TreatmentType::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => $data['name'],
                ],
                [
                    'color_hex' => $data['color_hex'],
                    'active' => true,
                ]
            );

            return [$data['name'] => $type];
        });

        $treatments = collect([
            ['name' => 'Consulta nutricional inicial', 'type' => 'Nutrición', 'duration' => 60],
            ['name' => 'Seguimiento nutricional', 'type' => 'Nutrición', 'duration' => 30],
            ['name' => 'Valoración inicial estética', 'type' => 'Valoración inicial', 'duration' => 60],
            ['name' => 'Limpieza facial profunda', 'type' => 'Faciales', 'duration' => 90],
            ['name' => 'Radiofrecuencia corporal', 'type' => 'Aparatología estética', 'duration' => 90],
            ['name' => 'Cavitación', 'type' => 'Aparatología estética', 'duration' => 60],
            ['name' => 'Masaje reductivo', 'type' => 'Tratamientos estéticos', 'duration' => 60],
            ['name' => 'Depilación láser zona chica', 'type' => 'Depilación láser', 'duration' => 30],
            ['name' => 'Depilación láser zona grande', 'type' => 'Depilación láser', 'duration' => 90],
        ])->mapWithKeys(function ($data) use ($branch, $types) {
            $type = $types[$data['type']];

            $treatment = Treatment::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => $data['name'],
                ],
                [
                    'treatment_type_id' => $type->id,
                    'category' => $type->name,
                    'color_hex' => $type->color_hex,
                    'duration_minutes' => $data['duration'],
                    'description' => 'Tratamiento demo para pruebas.',
                    'active' => true,
                ]
            );

            return [$data['name'] => $treatment];
        });

        $templatesData = [
            [
                'name' => 'Paquete Nutrición Mensual',
                'price' => 1800,
                'items' => [
                    ['treatment' => 'Consulta nutricional inicial', 'sessions' => 1],
                    ['treatment' => 'Seguimiento nutricional', 'sessions' => 3],
                ],
            ],
            [
                'name' => 'Paquete Reductivo 8 sesiones',
                'price' => 4200,
                'items' => [
                    ['treatment' => 'Radiofrecuencia corporal', 'sessions' => 4],
                    ['treatment' => 'Cavitación', 'sessions' => 4],
                ],
            ],
            [
                'name' => 'Paquete Facial Premium',
                'price' => 2600,
                'items' => [
                    ['treatment' => 'Limpieza facial profunda', 'sessions' => 3],
                    ['treatment' => 'Valoración inicial estética', 'sessions' => 1],
                ],
            ],
            [
                'name' => 'Paquete Depilación Láser',
                'price' => 3500,
                'items' => [
                    ['treatment' => 'Depilación láser zona chica', 'sessions' => 3],
                    ['treatment' => 'Depilación láser zona grande', 'sessions' => 3],
                ],
            ],
        ];

        $templates = collect();

        foreach ($templatesData as $templateData) {
            $template = PackageTemplate::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => $templateData['name'],
                ],
                [
                    'description' => 'Paquete demo para pruebas de tabla semanal.',
                    'total_price' => $templateData['price'],
                    'active' => true,
                ]
            );

            $template->items()->delete();

            foreach ($templateData['items'] as $index => $item) {
                $template->items()->create([
                    'treatment_id' => $treatments[$item['treatment']]->id,
                    'sessions_included' => $item['sessions'],
                    'sort_order' => $index,
                ]);
            }

            $templates->push($template);
        }

        $patientNames = [
            'Ana López', 'Brenda Martínez', 'Carla Hernández', 'Daniela Torres', 'Elena Ramírez',
            'Fernanda Ruiz', 'Gabriela Pérez', 'Hilda Morales', 'Ivonne Castillo', 'Jessica Sánchez',
            'Karla Mendoza', 'Laura Jiménez', 'Mariana Flores', 'Natalia Vargas', 'Olivia Navarro',
            'Patricia Rojas', 'Regina Silva', 'Sandra Ortega', 'Tania Romero', 'Valeria Cruz',
            'Ximena Aguilar', 'Yazmín Luna', 'Andrea Campos', 'Bianca Salinas', 'Claudia Reyes',
            'Diana Fuentes', 'Esmeralda Vega', 'Fátima Pineda', 'Gloria Acosta', 'Inés Medina',
            'Julieta León', 'Lorena Bravo', 'Mónica Estrada', 'Paola Robles', 'Renata Solís',
            'Sofía Cárdenas', 'Teresa Molina', 'Úrsula Franco', 'Vanessa Padilla', 'Wendy Arias',
        ];

        foreach ($patientNames as $index => $name) {
            $patient = Patient::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'full_name' => $name,
                ],
                [
                    'phone' => '22' . str_pad((string) random_int(10000000, 99999999), 8, '0'),
                    'notes' => 'Paciente demo #' . ($index + 1),
                    'active' => true,
                ]
            );

            $template = $templates[$index % $templates->count()];

            $package = $template->cloneToPatient($patient, $admin->id, [
                'started_on' => now()->subDays(random_int(1, 25))->toDateString(),
                'status' => 'active',
            ]);

            $amounts = [
                $package->package_total,
                $package->package_total / 2,
                500,
                1000,
                1500,
            ];

            $paymentAmount = min(
                (float) $package->package_total,
                (float) $amounts[$index % count($amounts)]
            );

            Payment::create([
                'branch_id' => $branch->id,
                'patient_id' => $patient->id,
                'patient_package_id' => $package->id,
                'amount' => $paymentAmount,
                'method' => ['efectivo', 'transferencia', 'tarjeta'][$index % 3],
                'paid_at' => now()->subDays(random_int(0, 20))->setTime(random_int(9, 18), 0),
                'reference' => 'DEMO-' . str_pad((string) $index + 1, 3, '0', STR_PAD_LEFT),
                'notes' => 'Pago demo.',
                'created_by' => $admin->id,
            ]);

            foreach ($package->items as $itemIndex => $packageItem) {
                $start = Carbon::now()
                    ->startOfWeek()
                    ->addDays(($index + $itemIndex) % 6)
                    ->setTime(9 + (($index + $itemIndex) % 8), 0);

                Appointment::create([
                    'branch_id' => $branch->id,
                    'patient_id' => $patient->id,
                    'patient_package_id' => $package->id,
                    'patient_package_item_id' => $packageItem->id,
                    'treatment_id' => $packageItem->treatment_id,
                    'specialist_id' => $specialists[($index + $itemIndex) % $specialists->count()]->id,
                    'created_by' => $admin->id,
                    'start_at' => $start,
                    'end_at' => $start->copy()->addMinutes($packageItem->treatment->duration_minutes ?? 60),
                    'status' => ['confirmed', 'completed', 'pending', 'cancelled', 'no_show'][$index % 5],
                    'notes' => 'Cita demo.',
                ]);
            }
        }
    }
}