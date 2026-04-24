@extends('layouts.app')

@section('title', ' | Pacientes')
@section('page_title', 'Pacientes')
@section('page_subtitle', 'Listado de pacientes y resumen de su paquete actual.')

@section('page_actions')
    <a href="{{ route('pacientes.create') }}" class="vf-btn-primary">
        + Nuevo paciente
    </a>
@endsection

@section('content')
    <div class="vf-card">
        <div class="border-b border-gray-200 p-5">
            <form method="GET" action="{{ route('pacientes.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="w-full md:max-w-md">
                    <label class="block text-xs font-medium text-gray-600">Buscar paciente</label>
                    <input type="text"
                           name="q"
                           value="{{ $q }}"
                           placeholder="Nombre o teléfono"
                           class="vf-input mt-1">
                </div>

                <div class="flex gap-2">
                    <button class="vf-btn-primary">Buscar</button>
                    <a href="{{ route('pacientes.index') }}" class="vf-btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="divide-y">
            @forelse($patients as $patient)
                @php
                    $activePackage = $patient->packagesNew->firstWhere('status', 'active')
                        ?? $patient->packagesNew->first();

                    $packagesCount = $patient->packagesNew->count();
                    $patientType = $packagesCount <= 1 ? 'Nuevo' : 'Reactivado';

                    $lastAppointment = $patient->appointments->first();
                @endphp

                <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-[260px_1fr_240px]">
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $patient->full_name }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $patient->phone ?: 'Sin teléfono' }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                {{ $patientType }}
                            </span>

                            @if($activePackage)
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium
                                    {{ $activePackage->status === 'active'
                                        ? 'border-green-200 bg-green-50 text-green-700'
                                        : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                                    {{ ucfirst($activePackage->status) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if($activePackage)
                            <div class="rounded-xl border border-gray-200 p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $activePackage->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            Asignado: {{ optional($activePackage->started_on)->format('d/m/Y') ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Total del paquete</p>
                                        <p class="font-semibold text-gray-900">
                                            ${{ number_format((float) $activePackage->package_total, 2) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    @foreach($activePackage->items as $item)
                                        @php
                                            $completed = $item->completed_sessions_count;
                                            $remaining = $item->remaining_sessions;
                                        @endphp

                                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                                            <div class="flex items-center gap-2">
                                                <span class="h-3 w-3 rounded"
                                                      style="background: {{ $item->treatment?->color_hex ?? '#9CA3AF' }}"></span>
                                                <span class="font-medium text-gray-800">
                                                    {{ $item->treatment?->name ?? 'Tratamiento eliminado' }}
                                                </span>
                                            </div>

                                            <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
                                                <div class="rounded-lg bg-white p-3 border border-gray-200">
                                                    <p class="text-xs text-gray-500">Incluidas</p>
                                                    <p class="font-semibold text-gray-900">{{ $item->sessions_included }}</p>
                                                </div>

                                                <div class="rounded-lg bg-white p-3 border border-gray-200">
                                                    <p class="text-xs text-gray-500">Usadas</p>
                                                    <p class="font-semibold text-gray-900">{{ $completed }}</p>
                                                </div>

                                                <div class="rounded-lg bg-white p-3 border border-gray-200">
                                                    <p class="text-xs text-gray-500">Restantes</p>
                                                    <p class="font-semibold text-gray-900">{{ $remaining }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-gray-500">
                                Sin paquetes asignados.
                            </div>
                        @endif
                    </div>

                    <div>
                        @if($lastAppointment)
                            <div class="rounded-xl border border-gray-200 p-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $lastAppointment->start_at->format('d/m/Y H:i') }}
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    {{ $lastAppointment->treatment?->name ?? 'Sin tratamiento' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $lastAppointment->specialist?->name ?? 'Sin especialista' }}
                                </p>

                                <div class="mt-3">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium
                                        {{ $lastAppointment->status === 'confirmed' ? 'border-blue-200 bg-blue-50 text-blue-700' : '' }}
                                        {{ $lastAppointment->status === 'completed' ? 'border-green-200 bg-green-50 text-green-700' : '' }}
                                        {{ in_array($lastAppointment->status, ['cancelled', 'no_show']) ? 'border-red-200 bg-red-50 text-red-700' : '' }}
                                        {{ $lastAppointment->status === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700' : '' }}">
                                        {{ ucfirst($lastAppointment->status) }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-gray-500">
                                Sin citas.
                            </div>
                        @endif

                        <div class="mt-4 flex items-center gap-4 text-sm">
                            <a href="{{ route('pacientes.edit', $patient) }}" class="font-medium text-[var(--vf-primary)] hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('pacientes.destroy', $patient) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar este paciente?')">
                                @csrf
                                @method('DELETE')
                                <button class="font-medium text-red-600 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">
                    No hay pacientes registrados.
                </div>
            @endforelse
        </div>

        @if($patients->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
@endsection