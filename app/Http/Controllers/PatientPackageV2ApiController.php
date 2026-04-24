<?php

namespace App\Http\Controllers;

use App\Models\Patient;

class PatientPackageV2ApiController extends Controller
{
    public function index(Patient $patient)
    {
        abort_unless((int) $patient->branch_id === current_branch_id(), 404);

        $packages = $patient->packagesNew()
            ->where('branch_id', current_branch_id())
            ->where('status', 'active')
            ->with(['items.appointments', 'items.treatment:id,name'])
            ->orderByDesc('id')
            ->get()
            ->filter(function ($package) {
                return $package->items->contains(function ($item) {
                    return $item->remaining_sessions > 0;
                });
            })
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'package_total' => (float) $package->package_total,
                    'label' => $package->name . ' — $' . number_format((float) $package->package_total, 2),
                ];
            })
            ->values();

        return response()->json($packages);
    }
}