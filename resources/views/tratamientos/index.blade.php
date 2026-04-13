@extends('layouts.app')

@section('title', ' | Tratamientos')
@section('page_title', 'Tratamientos')
@section('page_subtitle', 'Catálogo de tratamientos: tipo, duración y color (para agenda).')

@section('page_actions')
    <a href="{{ route('tratamientos.create') }}"
       class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
        + Nuevo tratamiento
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $treatments->total() }}</span>
            </p>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-600 border-b">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Tipo</th>
                        <th class="py-2 pr-4">Duración</th>
                        <th class="py-2 pr-4">Color</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($treatments as $t)
                    <tr>
                        <td class="py-3 pr-4 font-medium">{{ $t->name }}</td>
                        <td class="py-3 pr-4">{{ $t->category }}</td>
                        <td class="py-3 pr-4">{{ $t->duration_minutes }} min</td>
                        <td class="py-3 pr-4">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded" style="background: {{ $t->color_hex }}"></span>
                                <span class="text-gray-600">{{ $t->color_hex }}</span>
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            @if($t->active)
                                <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 border border-green-200">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 border border-gray-200">Inactivo</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <a href="{{ route('tratamientos.edit', $t) }}" class="text-gray-900 font-medium hover:underline">Editar</a>

                            <form action="{{ route('tratamientos.destroy', $t) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar este tratamiento?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-3 text-red-600 font-medium hover:underline" type="submit">Eliminar</button>
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
            <div class="p-5 border-t border-gray-200">
                {{ $treatments->links() }}
            </div>
        @endif
    </div>
@endsection
