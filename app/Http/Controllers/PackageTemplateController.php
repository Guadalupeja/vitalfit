<?php

namespace App\Http\Controllers;

use App\Models\PackageTemplate;
use App\Models\Treatment;
use Illuminate\Http\Request;

class PackageTemplateController extends Controller
{
    public function index()
    {
        $packages = PackageTemplate::query()
            ->where('branch_id', current_branch_id())
            ->with(['items.treatment:id,name'])
            ->orderByDesc('active')
            ->orderBy('name')
            ->paginate(15);

        return view('paquetes.index', compact('packages'));
    }

    public function create()
    {
        $treatments = Treatment::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        return view('paquetes.create', compact('treatments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'total_price' => ['required', 'numeric', 'gt:0'],
            'active' => ['nullable', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.treatment_id' => ['required', 'integer', 'exists:treatments,id', 'distinct'],
            'items.*.sessions_included' => ['required', 'integer', 'min:1'],
        ], [
            'name.required' => 'Debes escribir el nombre del paquete.',

            'total_price.required' => 'Debes capturar el total del paquete.',
            'total_price.numeric' => 'El total del paquete debe ser numérico.',
            'total_price.gt' => 'El total del paquete debe ser mayor a 0.',

            'items.required' => 'Debes agregar al menos un tratamiento al paquete.',
            'items.array' => 'Los tratamientos del paquete no tienen un formato válido.',
            'items.min' => 'Debes agregar al menos un tratamiento al paquete.',

            'items.*.treatment_id.required' => 'Debes seleccionar un tratamiento en cada fila.',
            'items.*.treatment_id.integer' => 'El tratamiento seleccionado no es válido.',
            'items.*.treatment_id.exists' => 'Uno de los tratamientos seleccionados no existe.',
            'items.*.treatment_id.distinct' => 'No puedes repetir el mismo tratamiento dentro del paquete.',

            'items.*.sessions_included.required' => 'Debes indicar las sesiones incluidas en cada tratamiento.',
            'items.*.sessions_included.integer' => 'Las sesiones incluidas deben ser un número entero.',
            'items.*.sessions_included.min' => 'Las sesiones incluidas deben ser al menos 1.',
        ]);

        $package = PackageTemplate::create([
            'branch_id' => current_branch_id(),
            'name' => trim($data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'total_price' => $data['total_price'],
            'active' => (bool) $request->boolean('active'),
        ]);

        foreach ($data['items'] as $index => $item) {
            $treatment = Treatment::query()
                ->where('branch_id', current_branch_id())
                ->find($item['treatment_id']);

            if (!$treatment) {
                return back()
                    ->withErrors([
                        "items.$index.treatment_id" => 'El tratamiento no pertenece a la sucursal activa.'
                    ])
                    ->withInput();
            }

            $package->items()->create([
                'treatment_id' => (int) $item['treatment_id'],
                'sessions_included' => (int) $item['sessions_included'],
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('paquetes.index')
            ->with('success', 'Paquete creado correctamente.');
    }

    public function edit(PackageTemplate $paquete)
    {
        abort_unless((int) $paquete->branch_id === current_branch_id(), 404);

        $paquete->load(['items.treatment:id,name,category']);

        $treatments = Treatment::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        return view('paquetes.edit', compact('paquete', 'treatments'));
    }

    public function update(Request $request, PackageTemplate $paquete)
    {
        abort_unless((int) $paquete->branch_id === current_branch_id(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'total_price' => ['required', 'numeric', 'gt:0'],
            'active' => ['nullable', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.treatment_id' => ['required', 'integer', 'exists:treatments,id', 'distinct'],
            'items.*.sessions_included' => ['required', 'integer', 'min:1'],
        ], [
            'name.required' => 'Debes escribir el nombre del paquete.',

            'total_price.required' => 'Debes capturar el total del paquete.',
            'total_price.numeric' => 'El total del paquete debe ser numérico.',
            'total_price.gt' => 'El total del paquete debe ser mayor a 0.',

            'items.required' => 'Debes agregar al menos un tratamiento al paquete.',
            'items.array' => 'Los tratamientos del paquete no tienen un formato válido.',
            'items.min' => 'Debes agregar al menos un tratamiento al paquete.',

            'items.*.treatment_id.required' => 'Debes seleccionar un tratamiento en cada fila.',
            'items.*.treatment_id.integer' => 'El tratamiento seleccionado no es válido.',
            'items.*.treatment_id.exists' => 'Uno de los tratamientos seleccionados no existe.',
            'items.*.treatment_id.distinct' => 'No puedes repetir el mismo tratamiento dentro del paquete.',

            'items.*.sessions_included.required' => 'Debes indicar las sesiones incluidas en cada tratamiento.',
            'items.*.sessions_included.integer' => 'Las sesiones incluidas deben ser un número entero.',
            'items.*.sessions_included.min' => 'Las sesiones incluidas deben ser al menos 1.',
        ]);

        $paquete->update([
            'name' => trim($data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'total_price' => $data['total_price'],
            'active' => (bool) $request->boolean('active'),
        ]);

        $paquete->items()->delete();

        foreach ($data['items'] as $index => $item) {
            $treatment = Treatment::query()
                ->where('branch_id', current_branch_id())
                ->find($item['treatment_id']);

            if (!$treatment) {
                return back()
                    ->withErrors([
                        "items.$index.treatment_id" => 'El tratamiento no pertenece a la sucursal activa.'
                    ])
                    ->withInput();
            }

            $paquete->items()->create([
                'treatment_id' => (int) $item['treatment_id'],
                'sessions_included' => (int) $item['sessions_included'],
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('paquetes.index')
            ->with('success', 'Paquete actualizado correctamente.');
    }

    public function destroy(PackageTemplate $paquete)
    {
        abort_unless((int) $paquete->branch_id === current_branch_id(), 404);

        $paquete->delete();

        return redirect()
            ->route('paquetes.index')
            ->with('success', 'Paquete eliminado.');
    }
}