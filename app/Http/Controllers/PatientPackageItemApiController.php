<?php

namespace App\Http\Controllers;

use App\Models\PatientPackage;

class PatientPackageItemApiController extends Controller
{
    public function index(PatientPackage $patientPackage)
    {
        abort_unless((int) $patientPackage->branch_id === current_branch_id(), 404);

        $items = $patientPackage->items()
            ->with(['treatment:id,name,color_hex,duration_minutes'])
            ->get()
            ->filter(function ($item) use ($patientPackage) {
                return $patientPackage->status === 'active' && $item->remaining_sessions > 0;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'treatment_id' => $item->treatment_id,
                    'treatment_name' => $item->treatment?->name,
                    'color_hex' => $item->treatment?->color_hex,
                    'duration_minutes' => (int) ($item->treatment?->duration_minutes ?? 60),
                    'sessions_included' => (int) $item->sessions_included,
                    'completed_sessions' => (int) $item->completed_sessions_count,
                    'remaining_sessions' => (int) $item->remaining_sessions,
                    'label' => ($item->treatment?->name ?? 'Tratamiento') . ' — ' . $item->remaining_sessions . ' sesiones disponibles',
                ];
            })
            ->values();

        return response()->json($items);
    }
}