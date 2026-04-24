<?php

namespace App\Http\Controllers;

use App\Http\Requests\TreatmentRequest;
use App\Models\Treatment;
use App\Models\TreatmentType;

class TreatmentController extends Controller
{
    public function index()
    {
        $treatments = Treatment::query()
            ->where('branch_id', current_branch_id())
            ->with('type:id,name,color_hex')
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('tratamientos.index', compact('treatments'));
    }

    public function create()
    {
        $treatmentTypes = TreatmentType::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color_hex']);

        return view('tratamientos.create', compact('treatmentTypes'));
    }

    public function store(TreatmentRequest $request)
    {
        $data = $request->validated();

        $type = TreatmentType::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->find($data['treatment_type_id']);

        if (! $type) {
            return back()
                ->withErrors([
                    'treatment_type_id' => 'El tipo de tratamiento no pertenece a la sucursal activa o está inactivo.'
                ])
                ->withInput();
        }

        Treatment::create([
            'branch_id' => current_branch_id(),
            'name' => trim($data['name']),
            'treatment_type_id' => $type->id,
            'category' => $type->name,       // temporal, por compatibilidad
            'color_hex' => $type->color_hex, // temporal, por compatibilidad
            'duration_minutes' => (int) $data['duration_minutes'],
            'description' => $data['description'] ?? null,
            'active' => (bool) $request->boolean('active', true),
        ]);

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento creado correctamente.');
    }

    public function edit(Treatment $tratamiento)
    {
        abort_unless((int) $tratamiento->branch_id === current_branch_id(), 404);

        $tratamiento->load('type:id,name,color_hex');

        $treatmentTypes = TreatmentType::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color_hex']);

        return view('tratamientos.edit', compact('tratamiento', 'treatmentTypes'));
    }

    public function update(TreatmentRequest $request, Treatment $tratamiento)
    {
        abort_unless((int) $tratamiento->branch_id === current_branch_id(), 404);

        $data = $request->validated();

        $type = TreatmentType::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->find($data['treatment_type_id']);

        if (! $type) {
            return back()
                ->withErrors([
                    'treatment_type_id' => 'El tipo de tratamiento no pertenece a la sucursal activa o está inactivo.'
                ])
                ->withInput();
        }

        $tratamiento->update([
            'name' => trim($data['name']),
            'treatment_type_id' => $type->id,
            'category' => $type->name,       // temporal, por compatibilidad
            'color_hex' => $type->color_hex, // temporal, por compatibilidad
            'duration_minutes' => (int) $data['duration_minutes'],
            'description' => $data['description'] ?? null,
            'active' => (bool) $request->boolean('active', false),
        ]);

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento actualizado correctamente.');
    }

    public function destroy(Treatment $tratamiento)
    {
        abort_unless((int) $tratamiento->branch_id === current_branch_id(), 404);

        $tratamiento->delete();

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento eliminado.');
    }
}