<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Models\PatientTreatment;

use App\Models\Treatment;

class PacienteController extends Controller
{
public function index(\Illuminate\Http\Request $request)
{
    $q = trim((string) $request->query('q', ''));

    $patients = \App\Models\Patient::query()
        ->with(['packages' => function ($query) {
            $query->with('treatment:id,name,color_hex')
                ->where('status', 'active')
                ->withSum('payments as total_paid', 'amount')
                ->withCount(['appointments as completed_sessions' => function ($qq) {
                    $qq->where('status', 'completed');
                }])
                ->orderByDesc('id');
        }])
        ->with(['appointments' => function ($query) {
            $query->latest('start_at')
                ->limit(1)
                ->with(['treatment:id,name', 'specialist:id,name']);
        }])
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
        $treatments = Treatment::query()
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'duration_minutes', 'color_hex']);

        return view('pacientes.create', compact('treatments'));
    }

    public function store(PatientRequest $request)
    {
    $data = $request->validate([
        'full_name' => ['required','string','max:255'],
        'phone' => ['nullable','string','max:50'],
        'notes' => ['nullable','string','max:2000'],
        'active' => ['nullable','boolean'],

        // estos vienen del form:
        'treatment_id' => ['nullable','integer','exists:treatments,id'],
        'sessions_purchased' => ['nullable','integer','min:1','max:999'],
        'package_total' => ['nullable','numeric','min:0','max:999999.99'],
    ]);

    // 1) Crear paciente (sin depender de los campos viejos)
    $patient = Patient::create([
        'full_name' => $data['full_name'],
        'phone' => $data['phone'] ?? null,
        'notes' => $data['notes'] ?? null,
        'active' => $data['active'] ?? true,
    ]);

    // 2) Si eligieron tratamiento + sesiones, crear paquete activo automático
    if (!empty($data['treatment_id']) && !empty($data['sessions_purchased'])) {
        PatientTreatment::create([
            'patient_id' => $patient->id,
            'treatment_id' => $data['treatment_id'],
            'sessions_purchased' => (int)$data['sessions_purchased'],
            'package_total' => (float)($data['package_total'] ?? 0),
            'status' => 'active',
            'started_on' => now()->toDateString(),
        ]);
    }

    return redirect()
        ->route('pacientes.index')
        ->with('success', 'Paciente creado correctamente.');
}

public function edit(\App\Models\Patient $paciente)
{
    $treatments = \App\Models\Treatment::query()
        ->where('active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get(['id','name','category','duration_minutes','color_hex']);

    // Paquetes del paciente con:
    // - tratamiento
    // - total pagado (sum payments)
    // - sesiones realizadas (count appointments completed)
    // - pagos listados (últimos)
    // - citas listadas (últimas)
    $packages = $paciente->packages()
        ->with('treatment:id,name,color_hex')
        ->withSum('payments as total_paid', 'amount')
        ->withCount(['appointments as completed_sessions' => function ($q) {
            $q->where('status', 'completed');
        }])
        ->with([
            'payments' => function ($q) {
                $q->with(['creator:id,name'])
                  ->orderByDesc('paid_at');
            },
            'appointments' => function ($q) {
                $q->with(['specialist:id,name'])
                  ->orderByDesc('start_at');
            },
        ])
        ->orderByDesc('id')
        ->get();

    return view('pacientes.edit', compact('paciente', 'treatments', 'packages'));
}

    public function update(PatientRequest $request, Patient $paciente)
    {
        $data = $request->validated();
        $data['active'] = (bool)($request->input('active', false)); // checkbox

        $paciente->update($data);

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente.');
    }

    public function destroy(Patient $paciente)
    {
        $paciente->delete();

        return redirect()
            ->route('pacientes.index')
            ->with('success', 'Paciente eliminado.');
    }
}
