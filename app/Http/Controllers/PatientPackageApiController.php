<?php

namespace App\Http\Controllers;

use App\Models\Patient;

class PatientPackageApiController extends Controller
{
    public function index(Patient $patient)
    {
        abort_unless((int)$patient->branch_id === current_branch_id(), 404);

        $packages = $patient->packages()
            ->where('branch_id', current_branch_id())
            ->where('status', 'active')
            ->with('treatment:id,name,color_hex,duration_minutes')
            ->withCount([
                'appointments as completed_sessions_count' => function ($q) {
                    $q->where('status', 'completed');
                }
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($pkg) {
                $remaining = max(0, (int)$pkg->sessions_purchased - (int)$pkg->completed_sessions_count);

                // Si ya no tiene sesiones, lo marcamos terminado
                if ($remaining <= 0 && $pkg->status === 'active') {
                    $pkg->update(['status' => 'finished']);
                }

                return [
                    'id' => $pkg->id,
                    'treatment_id' => $pkg->treatment_id,
                    'treatment_name' => $pkg->treatment->name,
                    'color_hex' => $pkg->treatment->color_hex,
                    'duration_minutes' => (int)($pkg->treatment->duration_minutes ?? 60),
                    'sessions_purchased' => (int)$pkg->sessions_purchased,
                    'completed_sessions' => (int)$pkg->completed_sessions_count,
                    'remaining_sessions' => $remaining,
                    'package_total' => (float)$pkg->package_total,
                    'label' => $pkg->treatment->name . ' — ' . $remaining . ' sesiones disponibles',
                ];
            })
            ->filter(fn ($pkg) => $pkg['remaining_sessions'] > 0)
            ->values();

        return response()->json($packages);
    }
}