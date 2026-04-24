<?php

namespace App\Http\Controllers;

use App\Http\Requests\TreatmentTypeRequest;
use App\Models\TreatmentType;

class TreatmentTypeController extends Controller
{
    public function index()
    {
        $treatmentTypes = TreatmentType::query()
            ->where('branch_id', current_branch_id())
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('tipos-tratamiento.index', compact('treatmentTypes'));
    }

    public function create()
    {
        return view('tipos-tratamiento.create');
    }

    public function store(TreatmentTypeRequest $request)
    {
        $data = $request->validated();

        TreatmentType::create([
            'branch_id' => current_branch_id(),
            'name' => trim($data['name']),
            'color_hex' => strtoupper($data['color_hex']),
            'active' => (bool) $request->boolean('active', true),
        ]);

        return redirect()
            ->route('tipos-tratamiento.index')
            ->with('success', 'Tipo de tratamiento creado correctamente.');
    }

    public function edit(TreatmentType $tipo_tratamiento)
    {
        abort_unless((int) $tipo_tratamiento->branch_id === current_branch_id(), 404);

        return view('tipos-tratamiento.edit', [
            'tipoTratamiento' => $tipo_tratamiento,
        ]);
    }

    public function update(TreatmentTypeRequest $request, TreatmentType $tipo_tratamiento)
    {
        abort_unless((int) $tipo_tratamiento->branch_id === current_branch_id(), 404);

        $data = $request->validated();

        $tipo_tratamiento->update([
            'name' => trim($data['name']),
            'color_hex' => strtoupper($data['color_hex']),
            'active' => (bool) $request->boolean('active', false),
        ]);

        return redirect()
            ->route('tipos-tratamiento.index')
            ->with('success', 'Tipo de tratamiento actualizado correctamente.');
    }

    public function destroy(TreatmentType $tipo_tratamiento)
    {
        abort_unless((int) $tipo_tratamiento->branch_id === current_branch_id(), 404);

        $tipo_tratamiento->delete();

        return redirect()
            ->route('tipos-tratamiento.index')
            ->with('success', 'Tipo de tratamiento eliminado.');
    }
}