@extends('layouts.app')

@section('title', ' | Tratamientos')
@section('page_title', 'Tratamientos')
@section('page_subtitle', 'Catálogo de tratamientos: tipo, duración y color definido por el tipo.')

@section('page_actions')
    <a href="{{ route('tratamientos.create') }}" class="vf-btn-primary">
        + Nuevo tratamiento
    </a>
@endsection

@section('content')
    <div class="vf-card">
        <div class="flex items-center justify-between border-b border-gray-200 p-5">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $treatments->total() }}</span>
            </p>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Tipo</th>
                        <th class="py-2 pr-4">Duración</th>
                        <th class="py-2 pr-4">Color del tipo</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($treatments as $t)
                    @php
                        $typeName = $t->type?->name ?? $t->category ?? 'Sin tipo';
                        $typeColor = $t->type?->color_hex ?? $t->color_hex ?? '#9CA3AF';
                    @endphp

                    <tr>
                        <td class="py-3 pr-4 font-medium text-gray-900">
                            {{ $t->name }}
                        </td>

                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-gray-200"
                                      style="background: {{ $typeColor }}"></span>
                                <span class="text-gray-700">{{ $typeName }}</span>
                            </div>
                        </td>

                        <td class="py-3 pr-4 text-gray-700">
                            {{ $t->duration_minutes }} min
                        </td>

                        <td class="py-3 pr-4">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-full border border-gray-300"
                                      style="background: {{ $typeColor }}"></span>
                                <span class="text-gray-600">{{ strtoupper($typeColor) }}</span>
                            </span>
                        </td>

                        <td class="py-3 pr-4">
                            @if($t->active)
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
                            <a href="{{ route('tratamientos.edit', $t) }}"
                               class="font-medium text-[var(--vf-primary)] hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('tratamientos.destroy', $t) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar este tratamiento?')">
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
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            No hay tratamientos. Crea el primero.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($treatments->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $treatments->links() }}
            </div>
        @endif
    </div>
@endsection