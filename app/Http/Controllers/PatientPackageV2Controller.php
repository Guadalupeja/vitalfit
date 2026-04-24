<?php

namespace App\Http\Controllers;

use App\Models\PatientPackage;
use App\Models\Treatment;
use Illuminate\Http\Request;

class PatientPackageV2Controller extends Controller
{
    public function update(Request $request, PatientPackage $patientPackage)
    {
        abort_unless((int) $patientPackage->branch_id === current_branch_id(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'package_total' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'in:active,paused,finished,cancelled'],
            'started_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.sessions_included' => ['required', 'integer', 'min:1'],
        ], [
            'name.required' => 'Debes capturar el nombre del paquete.',
            'package_total.required' => 'Debes capturar el total del paquete.',
            'package_total.gt' => 'El total del paquete debe ser mayor a 0.',
            'items.required' => 'El paquete debe tener al menos un tratamiento.',
            'items.min' => 'El paquete debe tener al menos un tratamiento.',
            'items.*.sessions_included.required' => 'Debes capturar las sesiones de cada tratamiento.',
            'items.*.sessions_included.min' => 'Las sesiones deben ser al menos 1.',
        ]);

        $patientPackage->update([
            'name' => trim($data['name']),
            'package_total' => $data['package_total'],
            'status' => $data['status'],
            'started_on' => $data['started_on'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $items = $patientPackage->items()->get()->keyBy('id');

        foreach ($data['items'] as $itemData) {
            $itemId = (int) $itemData['id'];

            if (!isset($items[$itemId])) {
                continue;
            }

            $items[$itemId]->update([
                'sessions_included' => (int) $itemData['sessions_included'],
            ]);
        }

        $patientPackage->refresh();

        return redirect()
            ->route('pacientes.edit', $patientPackage->patient_id)
            ->with('success', 'Paquete del paciente actualizado correctamente.');
    }

    public function destroy(PatientPackage $patientPackage)
    {
        abort_unless((int) $patientPackage->branch_id === current_branch_id(), 404);

        $patientId = $patientPackage->patient_id;

        $patientPackage->delete();

        return redirect()
            ->route('pacientes.edit', $patientId)
            ->with('success', 'Paquete del paciente eliminado correctamente.');
    }
}