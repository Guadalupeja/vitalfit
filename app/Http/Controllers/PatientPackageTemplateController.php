<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PackageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPackageTemplateController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        abort_unless((int) $patient->branch_id === current_branch_id(), 404);

        $data = $request->validate([
            'package_template_id' => ['required', 'integer', 'exists:package_templates,id'],
            'name' => ['nullable', 'string', 'max:150'],
            'package_total' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'in:active,paused,finished,cancelled'],
            'started_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'package_template_id.required' => 'Debes seleccionar un paquete del catálogo.',
            'package_total.required' => 'Debes capturar el total del paquete.',
            'package_total.numeric' => 'El total del paquete debe ser numérico.',
            'package_total.gt' => 'El total del paquete debe ser mayor a 0.',
        ]);

        $template = PackageTemplate::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->with('items')
            ->find($data['package_template_id']);

        if (! $template) {
            return back()
                ->withErrors([
                    'package_template_id' => 'El paquete no pertenece a la sucursal activa o está inactivo.'
                ])
                ->withInput();
        }

        if ($template->items->isEmpty()) {
            return back()
                ->withErrors([
                    'package_template_id' => 'El paquete seleccionado no tiene tratamientos configurados.'
                ])
                ->withInput();
        }

        $template->cloneToPatient($patient, Auth::id(), [
            'name' => !empty($data['name']) ? $data['name'] : $template->name,
            'package_total' => $data['package_total'],
            'status' => $data['status'],
            'started_on' => $data['started_on'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('pacientes.edit', $patient)
            ->with('success', 'Paquete asignado correctamente al paciente.');
    }
}