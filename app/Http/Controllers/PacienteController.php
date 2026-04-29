<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
public function index(Request $request)
{
    $q = trim((string) $request->query('q', ''));

    $patients = Patient::query()
        ->where('branch_id', current_branch_id())
        ->with([
            'packagesNew' => function ($query) {
                $query->where('branch_id', current_branch_id())
                    ->with([
                        'items.treatment:id,name,color_hex',
                    ])
                    ->withSum('payments as total_paid', 'amount')
                    ->orderByRaw("
                        CASE 
                            WHEN status = 'active' THEN 0
                            WHEN status = 'paused' THEN 1
                            WHEN status = 'finished' THEN 2
                            WHEN status = 'cancelled' THEN 3
                            ELSE 4
                        END
                    ")
                    ->orderByDesc('id');
            },
        ])
        ->with([
            'appointments' => function ($query) {
                $query->where('branch_id', current_branch_id())
                    ->latest('start_at')
                    ->limit(1)
                    ->with(['treatment:id,name,color_hex', 'specialist:id,name']);
            },
        ])
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        })
        ->orderBy('active', 'desc')
        ->orderBy('full_name')
        ->paginate(15)
        ->withQueryString();

    return view('pacientes.index', compact('patients', 'q'));
}

public function create()
{
    $packageTemplates = \App\Models\PackageTemplate::query()
        ->where('branch_id', current_branch_id())
        ->where('active', true)
        ->with(['items.treatment:id,name,color_hex'])
        ->orderBy('name')
        ->get();

    return view('pacientes.create', compact('packageTemplates'));
}

public function store(PatientRequest $request)
{
    $data = $request->validated();

    $patient = Patient::create([
        'branch_id' => current_branch_id(),
        'full_name' => $data['full_name'],
        'phone' => $data['phone'] ?? null,
        'notes' => $data['notes'] ?? null,
        'active' => (bool)($request->input('active', true)),
    ]);

    if (!empty($data['package_template_id'])) {
        $template = \App\Models\PackageTemplate::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->with('items')
            ->find($data['package_template_id']);

        if (!$template) {
            return back()
                ->withErrors([
                    'package_template_id' => 'El paquete seleccionado no pertenece a la sucursal activa o está inactivo.'
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

$template->cloneToPatient($patient, Auth::id(), [            'name' => $template->name,
            'package_total' => $template->total_price,
            'status' => 'active',
            'started_on' => now()->toDateString(),
            'notes' => null,
        ]);
    }

    return redirect()
        ->route('pacientes.index')
        ->with('success', 'Paciente creado correctamente.');
}

public function edit(Patient $paciente)
{
    abort_unless((int)$paciente->branch_id === current_branch_id(), 404);

    $treatments = Treatment::query()
        ->where('active', true)
        ->where('branch_id', current_branch_id())
        ->orderBy('category')
        ->orderBy('name')
        ->get(['id', 'name', 'category', 'duration_minutes', 'color_hex']);

    $packageTemplates = \App\Models\PackageTemplate::query()
        ->where('branch_id', current_branch_id())
        ->where('active', true)
        ->with(['items.treatment:id,name,color_hex'])
        ->orderBy('name')
        ->get();

    $patientPackages = $paciente->packagesNew()
        ->where('branch_id', current_branch_id())
        ->with([
            'items.treatment:id,name,color_hex',
            'creator:id,name',
        ])
        ->orderByDesc('id')
        ->get();

    // Temporal: mantener paquetes viejos mientras migras
    $packages = $paciente->packages()
        ->where('branch_id', current_branch_id())
        ->with('treatment:id,name,color_hex')
        ->withSum('payments as total_paid', 'amount')
        ->withCount(['appointments as completed_sessions' => function ($q) {
            $q->where('status', 'completed');
        }])
        ->with([
            'payments' => function ($q) {
                $q->where('branch_id', current_branch_id())
                  ->with(['creator:id,name'])
                  ->orderByDesc('paid_at');
            },
            'appointments' => function ($q) {
                $q->where('branch_id', current_branch_id())
                  ->with(['specialist:id,name'])
                  ->orderByDesc('start_at');
            },
        ])
        ->orderByDesc('id')
        ->get();

    return view('pacientes.edit', compact(
        'paciente',
        'treatments',
        'packages',
        'packageTemplates',
        'patientPackages'
    ));
}

    public function update(PatientRequest $request, Patient $paciente)
    {
        abort_unless((int)$paciente->branch_id === current_branch_id(), 404);

        $data = $request->validated();

        $paciente->update([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'active' => (bool)($request->input('active', false)),
        ]);

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente.');
    }

    public function destroy(Patient $paciente)
    {
        abort_unless((int)$paciente->branch_id === current_branch_id(), 404);

        $paciente->delete();

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente eliminado.');
    }
}