<?php

namespace App\Http\Controllers;

use App\Http\Requests\TreatmentRequest;
use App\Models\Treatment;

class TreatmentController extends Controller
{
    public function index()
    {
        $treatments = Treatment::query()
            ->orderBy('active', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15);

        return view('tratamientos.index', compact('treatments'));
    }

    public function create()
    {
        $categories = $this->categories();
        return view('tratamientos.create', compact('categories'));
    }

    public function store(TreatmentRequest $request)
    {
        $data = $request->validated();
        $data['active'] = (bool)($request->input('active', true));

        Treatment::create($data);

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento creado correctamente.');
    }

    public function edit(Treatment $tratamiento)
    {
        $categories = $this->categories();
        return view('tratamientos.edit', compact('tratamiento', 'categories'));
    }

    public function update(TreatmentRequest $request, Treatment $tratamiento)
    {
        $data = $request->validated();
        $data['active'] = (bool)($request->input('active', false)); // checkbox

        $tratamiento->update($data);

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento actualizado correctamente.');
    }

    public function destroy(Treatment $tratamiento)
    {
        $tratamiento->delete();

        return redirect()
            ->route('tratamientos.index')
            ->with('success', 'Tratamiento eliminado.');
    }

    private function categories(): array
    {
        return [
            'faciales' => 'Faciales',
            'aparatologia' => 'Aparatología',
            'esteticos' => 'Tratamientos estéticos',
            'laser' => 'Depilación láser',
            'nutricion' => 'Nutrición',
            'valoracion' => 'Valoración inicial',
        ];
    }
}
