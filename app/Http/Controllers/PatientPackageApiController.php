<?php

namespace App\Http\Controllers;

use App\Models\Patient;

class PatientPackageApiController extends Controller
{
    public function index(Patient $patient)
    {
        $items = $patient->packages()
            ->with('treatment:id,name,color_hex,duration_minutes')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get()
            ->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'treatment_id' => $pkg->treatment_id,
                    'treatment_name' => $pkg->treatment->name,
                    'color_hex' => $pkg->treatment->color_hex,
                    'duration_minutes' => (int)($pkg->treatment->duration_minutes ?? 60),
                    'sessions_purchased' => (int)$pkg->sessions_purchased,
                    'package_total' => (float)$pkg->package_total,
                    'label' => $pkg->treatment->name . ' — ' . $pkg->sessions_purchased . ' sesiones',
                ];
            });

        return response()->json($items);
    }
}
