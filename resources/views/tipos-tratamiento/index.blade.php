@extends('layouts.app')

@section('title', ' | Tipos de tratamiento')
@section('page_title', 'Tipos de tratamiento')
@section('page_subtitle', 'Catálogo de tipos y colores usados en agenda y tratamientos.')

@section('page_actions')
    <a href="{{ route('tipos-tratamiento.create') }}" class="vf-btn-primary">
        + Nuevo tipo
    </a>
@endsection

@section('content')
    <div class="vf-card">
        <div class="flex items-center justify-between border-b border-gray-200 p-5">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $treatmentTypes->total() }}</span>
            </p>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Color</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($treatmentTypes as $type)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-gray-900">{{ $type->name }}</td>
                        <td class="py-3 pr-4">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-full border border-gray-300"
                                      style="background: {{ $type->color_hex }}"></span>
                                <span class="text-gray-600">{{ strtoupper($type->color_hex) }}</span>
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            @if($type->active)
                                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <a href="{{ route('tipos-tratamiento.edit', $type) }}"
                               class="font-medium text-[var(--vf-primary)] hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('tipos-tratamiento.destroy', $type) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar este tipo de tratamiento?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-3 font-medium text-red-600 hover:underline" type="submit">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-500">
                            No hay tipos de tratamiento. Crea el primero.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($treatmentTypes->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $treatmentTypes->links() }}
            </div>
        @endif
    </div>
@endsection