@extends('layouts.app')

@section('title', ' | Editar paciente')
@section('page_title', 'Editar paciente')
@section('page_subtitle', 'Actualizar información del paciente.')

@section('content')
    <div class="vf-card p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-medium">Hay errores en el formulario:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pacientes.update', $paciente) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('pacientes._form', ['paciente' => $paciente, 'treatments' => $treatments])

            <div class="flex items-center gap-2">
                <a href="{{ route('pacientes.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    {{-- Asignar paquete desde catálogo --}}
    <div class="mt-8 vf-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-semibold">Asignar paquete desde catálogo</p>
                <p class="text-sm text-gray-500">
                    Copia una plantilla de paquete al paciente y permite editarla después.
                </p>
            </div>
        </div>

        <form method="POST"
              action="{{ route('pacientes.paquetes_desde_catalogo.store', $paciente) }}"
              class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600">Paquete del catálogo</label>
                <select name="package_template_id" class="vf-input mt-1" required>
                    <option value="">— Selecciona —</option>
                    @foreach($packageTemplates as $template)
                        <option value="{{ $template->id }}" @selected(old('package_template_id') == $template->id)>
                            {{ $template->name }} — ${{ number_format((float) $template->total_price, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('package_template_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Nombre final del paquete</label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="vf-input mt-1"
                       placeholder="Opcional, si deseas cambiar el nombre">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Total del paquete ($)</label>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       name="package_total"
                       value="{{ old('package_total') }}"
                       class="vf-input mt-1"
                       required>
                @error('package_total')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Estatus</label>
                <select name="status" class="vf-input mt-1">
                    <option value="active" @selected(old('status', 'active') === 'active')>Activo</option>
                    <option value="paused" @selected(old('status') === 'paused')>Pausado</option>
                    <option value="finished" @selected(old('status') === 'finished')>Finalizado</option>
                    <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelado</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Fecha de asignación</label>
                <input type="date"
                       name="started_on"
                       value="{{ old('started_on', now()->toDateString()) }}"
                       class="vf-input mt-1">
                @error('started_on')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600">Notas</label>
                <textarea name="notes" rows="3" class="vf-input mt-1">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button class="vf-btn-primary">
                    Asignar paquete
                </button>
            </div>
        </form>
    </div>

    {{-- Paquetes nuevos del paciente --}}
<div class="mt-8 vf-card p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-semibold">Paquetes del paciente</p>
            <p class="text-sm text-gray-500">
                Paquetes compuestos con varios tratamientos y sesiones independientes.
            </p>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        @forelse($patientPackages as $package)
            <div class="rounded-xl border border-gray-200 p-5">
                <form method="POST" action="{{ route('patient_packages_v2.update', $package) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Nombre del paquete</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $package->name) }}"
                                   class="vf-input mt-1"
                                   required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Total del paquete ($)</label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   name="package_total"
                                   value="{{ old('package_total', $package->package_total) }}"
                                   class="vf-input mt-1"
                                   required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Estatus</label>
                            <select name="status" class="vf-input mt-1">
                                <option value="active" @selected($package->status === 'active')>Activo</option>
                                <option value="paused" @selected($package->status === 'paused')>Pausado</option>
                                <option value="finished" @selected($package->status === 'finished')>Finalizado</option>
                                <option value="cancelled" @selected($package->status === 'cancelled')>Cancelado</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Fecha de asignación</label>
                            <input type="date"
                                   name="started_on"
                                   value="{{ optional($package->started_on)->format('Y-m-d') }}"
                                   class="vf-input mt-1">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Notas</label>
                            <textarea name="notes" rows="3" class="vf-input mt-1">{{ old('notes', $package->notes) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <p class="mb-3 font-medium text-gray-900">Tratamientos incluidos</p>

                        <div class="overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Tratamiento</th>
                                        <th class="py-2 pr-4">Sesiones incluidas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($package->items as $item)
                                        <tr>
                                            <td class="py-3 pr-4">
                                                <span class="inline-flex items-center gap-2">
                                                    <span class="h-3 w-3 rounded"
                                                          style="background: {{ $item->treatment?->color_hex ?? '#9CA3AF' }}"></span>
                                                    <span class="font-medium">
                                                        {{ $item->treatment?->name ?? 'Tratamiento eliminado' }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="py-3 pr-4">
                                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                                <input type="number"
                                                       min="1"
                                                       name="items[{{ $loop->index }}][sessions_included]"
                                                       value="{{ $item->sessions_included }}"
                                                       class="vf-input w-28">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">
                            Creado por: {{ $package->creator?->name ?? '—' }}
                        </p>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="vf-btn-primary">
                                Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST"
                      action="{{ route('patient_packages_v2.destroy', $package) }}"
                      class="mt-3"
                      onsubmit="return confirm('¿Eliminar este paquete del paciente?')">
                    @csrf
                    @method('DELETE')

                    <button class="text-sm font-medium text-red-600 hover:underline">
                        Eliminar paquete
                    </button>
                </form>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500">
                Aún no hay paquetes nuevos asignados a este paciente.
            </div>
        @endforelse
    </div>
</div>


@endsection