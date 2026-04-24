@extends('layouts.app')

@section('title', ' | Paquetes')
@section('page_title', 'Paquetes')
@section('page_subtitle', 'Catálogo reutilizable de paquetes con varios tratamientos y sesiones.')

@section('page_actions')
    <a href="{{ route('paquetes.create') }}" class="vf-btn-primary">
        + Nuevo paquete
    </a>
@endsection

@section('content')
    <div class="vf-card">
        <div class="flex items-center justify-between border-b border-gray-200 p-5">
            <p class="text-sm text-gray-600">
                Total:
                <span class="font-medium text-gray-900">{{ $packages->total() }}</span>
            </p>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Tratamientos incluidos</th>
                        <th class="py-2 pr-4">Total</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($packages as $package)
                    <tr>
                        <td class="py-4 pr-4 align-top">
                            <p class="font-medium text-gray-900">{{ $package->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $package->description ?: 'Sin descripción' }}</p>
                        </td>

                        <td class="py-4 pr-4 align-top">
                            <div class="space-y-1">
                                @foreach($package->items as $item)
                                    <div class="text-sm text-gray-700">
                                        {{ $item->treatment?->name ?? 'Tratamiento eliminado' }}
                                        <span class="text-gray-500">— {{ $item->sessions_included }} sesiones</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="py-4 pr-4 align-top font-medium text-gray-900">
                            ${{ number_format((float)$package->total_price, 2) }}
                        </td>

                        <td class="py-4 pr-4 align-top">
                            @if($package->active)
                                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <td class="py-4 pr-4 align-top whitespace-nowrap">
                            <a href="{{ route('paquetes.edit', $package) }}" class="font-medium text-[var(--vf-primary)] hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('paquetes.destroy', $package) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar este paquete?')">
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
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            No hay paquetes registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($packages->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
@endsection