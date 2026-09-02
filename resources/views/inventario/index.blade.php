@extends('layouts.app')

@section('title', ' | Inventario')
@section('page_title', 'Inventario')
@section('page_subtitle', 'Control de productos por sucursal.')

@section('page_actions')
    <a href="{{ route('inventario.create') }}" class="vf-btn-primary">
        + Nuevo producto
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-xs text-gray-500">Productos encontrados</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalItems }}</p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs text-red-700">Bajo stock</p>
            <p class="mt-1 text-2xl font-semibold text-red-800">{{ $lowStockCount }}</p>
        </div>

        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
            <p class="text-xs text-orange-700">Caducados</p>
            <p class="mt-1 text-2xl font-semibold text-orange-800">{{ $expiredCount }}</p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs text-amber-700">Caducan en 30 días</p>
            <p class="mt-1 text-2xl font-semibold text-amber-800">{{ $expiresSoonCount }}</p>
        </div>
    </div>

    <div class="vf-card">
        <div class="border-b border-gray-200 p-5">
            <form method="GET" action="{{ route('inventario.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_220px_auto] lg:items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-600">Buscar producto</label>
                    <input type="text"
                           name="q"
                           value="{{ $q }}"
                           placeholder="Producto, presentación, segmento o notas"
                           class="vf-input mt-1">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">Segmento</label>
                    <select name="segment" class="vf-input mt-1">
                        <option value="">Todos</option>
                        @foreach($segments as $seg)
                            <option value="{{ $seg }}" @selected($segment === $seg)>
                                {{ $seg }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">Estatus</label>
                    <select name="status" class="vf-input mt-1">
                        <option value="" @selected($status === '')>Todos</option>
                        <option value="active" @selected($status === 'active')>Activos</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactivos</option>
                        <option value="low_stock" @selected($status === 'low_stock')>Bajo stock</option>
                        <option value="expired" @selected($status === 'expired')>Caducados</option>
                        <option value="expires_soon" @selected($status === 'expires_soon')>Caducan pronto</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button class="vf-btn-primary">Filtrar</button>
                    <a href="{{ route('inventario.index') }}" class="vf-btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Producto</th>
                        <th class="py-2 pr-4">Segmento</th>
                        <th class="py-2 pr-4">Cantidad</th>
                        <th class="py-2 pr-4">Stock mínimo</th>
                        <th class="py-2 pr-4">Entrada</th>
                        <th class="py-2 pr-4">Caducidad</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Actualizó</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($items as $item)
                        <tr class="align-top">
                            <td class="py-3 pr-4">
                                <p class="font-semibold text-gray-900">{{ $item->product }}</p>

                                @if($item->presentation)
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $item->presentation }}
                                    </p>
                                @endif

                                @if($item->notes)
                                    <p class="mt-1 max-w-xs text-xs text-gray-500">
                                        {{ $item->notes }}
                                    </p>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-gray-600">
                                {{ $item->segment ?: '—' }}
                            </td>

                            <td class="py-3 pr-4">
                                <p class="font-semibold {{ $item->is_low_stock ? 'text-red-700' : 'text-gray-900' }}">
                                    {{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}
                                </p>

                                @if($item->is_low_stock)
                                    <span class="mt-1 inline-flex rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                        Bajo stock
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-gray-600">
                                {{ $item->minimum_stock !== null ? number_format((float) $item->minimum_stock, 2) . ' ' . $item->unit : '—' }}
                            </td>

                            <td class="py-3 pr-4 text-gray-600">
                                {{ optional($item->entry_date)->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="py-3 pr-4">
                                @if($item->expiration_date)
                                    <p class="{{ $item->is_expired ? 'font-semibold text-red-700' : 'text-gray-600' }}">
                                        {{ $item->expiration_date->format('d/m/Y') }}
                                    </p>

                                    @if($item->is_expired)
                                        <span class="mt-1 inline-flex rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                            Caducado
                                        </span>
                                    @elseif($item->expires_soon)
                                        <span class="mt-1 inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                            Caduca pronto
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="py-3 pr-4">
                                @if($item->active)
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-gray-600">
                                <p>{{ $item->updater?->name ?? $item->creator?->name ?? '—' }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $item->updated_at?->format('d/m/Y H:i') }}
                                </p>
                            </td>

                            <td class="py-3 pr-4 whitespace-nowrap">
                                <a href="{{ route('inventario.edit', $item) }}"
                                   class="font-medium text-[var(--vf-primary)] hover:underline">
                                    Editar
                                </a>

                                <form action="{{ route('inventario.destroy', $item) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('¿Eliminar este producto del inventario?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="ml-3 font-medium text-red-600 hover:underline">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-gray-500">
                                No hay productos registrados en inventario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection