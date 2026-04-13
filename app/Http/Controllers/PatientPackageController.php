<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Treatment;
use Illuminate\Http\Request;

class PatientPackageController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'treatment_id' => ['required','integer','exists:treatments,id'],
            'sessions_purchased' => ['required','integer','min:1','max:999'],
            'package_total' => ['required','numeric','min:0','max:999999.99'],
            'status' => ['required','in:active,paused,finished,cancelled'],
            'started_on' => ['nullable','date'],
            'ends_on' => ['nullable','date'],
            'notes' => ['nullable','string','max:2000'],
        ]);

        $data['patient_id'] = $patient->id;

        PatientTreatment::create($data);

        return back()->with('success', 'Paquete agregado correctamente.');
    }

    public function update(Request $request, PatientTreatment $package)
    {
        $data = $request->validate([
            'sessions_purchased' => ['required','integer','min:1','max:999'],
            'package_total' => ['required','numeric','min:0','max:999999.99'],
            'status' => ['required','in:active,paused,finished,cancelled'],
            'started_on' => ['nullable','date'],
            'ends_on' => ['nullable','date'],
            'notes' => ['nullable','string','max:2000'],
        ]);

        $package->update($data);

        return back()->with('success', 'Paquete actualizado.');
    }

    public function destroy(PatientTreatment $package)
    {
        $package->delete();
        return back()->with('success', 'Paquete eliminado.');
    }
}
