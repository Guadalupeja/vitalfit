@extends('layouts.app')

@section('title', ' | Tabla semanal')
@section('page_title', 'Tabla semanal')
@section('page_subtitle', 'Actividad semanal por paciente: citas, nuevas asignaciones, pagos y avance de paquetes.')

@section('page_actions')
    <div class="no-print flex w-full flex-col gap-2">
        <form method="GET" action="{{ route('tabla_semanal.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600">Semana (lunes)</label>
                <input type="date" name="week_start" value="{{ $weekStart->format('Y-m-d') }}" class="vf-input mt-1">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Especialista</label>
                <select name="specialist_id" class="vf-input mt-1">
                    <option value="">Todos</option>
                    @foreach($specialists as $s)
                        <option value="{{ $s->id }}" @selected((string)$specialistId === (string)$s->id)>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Tratamiento</label>
                <select name="treatment_id" class="vf-input mt-1">
                    <option value="">Todos</option>
                    @foreach($treatments as $t)
                        <option value="{{ $t->id }}" @selected((string)$treatmentId === (string)$t->id)>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Estatus</label>
                <select name="status" class="vf-input mt-1">
                    <option value="">Todos</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmada</option>
                    <option value="completed" @selected($status === 'completed')>Realizada</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelada</option>
                    <option value="no_show" @selected($status === 'no_show')>No show</option>
                    <option value="pending" @selected($status === 'pending')>Pendiente</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button class="vf-btn-primary">Filtrar</button>
                <a href="{{ route('tabla_semanal.index') }}" class="vf-btn-secondary">Limpiar</a>
                <button type="button" onclick="window.print()" class="vf-btn-secondary no-print">Imprimir</button>
            </div>
        </form>

        <div>
            <a href="{{ route('agenda.index') }}" class="vf-btn-secondary">
                Ir a Agenda
            </a>
        </div>
    </div>
@endsection

@section('content')
@php
    $printLogo = asset('img/brand/LOGOS VITALFIT-04.png');
@endphp
    {{-- VISTA PANTALLA --}}
    <div class="screen-only">
        @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="no-print mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="vf-card p-5">
                    <h3 class="font-semibold text-gray-900">
                        Resumen semanal ({{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m') }})
                    </h3>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs text-gray-500">Ingresos de la semana</p>
                            <p class="text-lg font-semibold text-gray-900">${{ number_format($weeklyIncome ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs text-gray-500">Canceladas / No-show</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $weeklyCancelledAppointments?->count() ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Top paquetes por ingresos</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Paquete</th>
                                        <th class="py-2 pr-4">Paquetes</th>
                                        <th class="py-2 pr-4">Pagos</th>
                                        <th class="py-2 pr-4">Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($weeklyTopPackagesByIncome ?? [] as $row)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                            <td class="py-2 pr-4 text-gray-600">{{ $row->packages_count }}</td>
                                            <td class="py-2 pr-4 text-gray-600">{{ $row->payments_count }}</td>
                                            <td class="py-2 pr-4 font-medium text-gray-900">
                                                ${{ number_format($row->total_income, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-gray-500">Sin pagos registrados en esta semana.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Citas cumplidas (completed) por tratamiento</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Tratamiento</th>
                                        <th class="py-2 pr-4">Citas cumplidas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($weeklyCompletedByTreatment ?? [] as $row)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                            <td class="py-2 pr-4 font-medium text-gray-900">{{ $row->completed_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="py-6 text-center text-gray-500">Sin citas cumplidas en esta semana.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Canceladas / No-show (detalle)</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Fecha</th>
                                        <th class="py-2 pr-4">Paciente</th>
                                        <th class="py-2 pr-4">Tratamiento</th>
                                        <th class="py-2 pr-4">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($weeklyCancelledAppointments ?? [] as $a)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->start_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->patient?->full_name ?? '—' }}</td>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->treatment?->name ?? '—' }}</td>
                                            <td class="py-2 pr-4">
                                                <span class="inline-flex rounded-full border px-2 py-1 text-xs font-medium
                                                    {{ $a->status === 'no_show' ? 'border-red-200 bg-red-50 text-red-700' : 'border-orange-200 bg-orange-50 text-orange-700' }}">
                                                    {{ $a->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-gray-500">Sin cancelaciones/no-show en esta semana.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="vf-card p-5">
                    <h3 class="font-semibold text-gray-900">
                        Resumen mensual ({{ $monthStart->format('d/m') }} - {{ $monthEnd->format('d/m') }})
                    </h3>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs text-gray-500">Ingresos del mes</p>
                            <p class="text-lg font-semibold text-gray-900">${{ number_format($monthlyIncome ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs text-gray-500">Canceladas / No-show</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $monthlyCancelledAppointments?->count() ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Top paquetes por ingresos</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Paquete</th>
                                        <th class="py-2 pr-4">Paquetes</th>
                                        <th class="py-2 pr-4">Pagos</th>
                                        <th class="py-2 pr-4">Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($monthlyTopPackagesByIncome ?? [] as $row)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                            <td class="py-2 pr-4 text-gray-600">{{ $row->packages_count }}</td>
                                            <td class="py-2 pr-4 text-gray-600">{{ $row->payments_count }}</td>
                                            <td class="py-2 pr-4 font-medium text-gray-900">
                                                ${{ number_format($row->total_income, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-gray-500">Sin pagos registrados en este mes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Citas cumplidas (completed) por tratamiento</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Tratamiento</th>
                                        <th class="py-2 pr-4">Citas cumplidas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($monthlyCompletedByTreatment ?? [] as $row)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                            <td class="py-2 pr-4 font-medium text-gray-900">{{ $row->completed_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="py-6 text-center text-gray-500">Sin citas cumplidas en este mes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900">Canceladas / No-show (detalle)</h4>
                        <div class="mt-2 overflow-x-auto">
                            <table class="vf-table min-w-full text-sm">
                                <thead class="border-b text-left text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">Fecha</th>
                                        <th class="py-2 pr-4">Paciente</th>
                                        <th class="py-2 pr-4">Tratamiento</th>
                                        <th class="py-2 pr-4">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($monthlyCancelledAppointments ?? [] as $a)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->start_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->patient?->full_name ?? '—' }}</td>
                                            <td class="py-2 pr-4 text-gray-700">{{ $a->treatment?->name ?? '—' }}</td>
                                            <td class="py-2 pr-4">
                                                <span class="inline-flex rounded-full border px-2 py-1 text-xs font-medium
                                                    {{ $a->status === 'no_show' ? 'border-red-200 bg-red-50 text-red-700' : 'border-orange-200 bg-orange-50 text-orange-700' }}">
                                                    {{ $a->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-gray-500">Sin cancelaciones/no-show en este mes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="vf-card">
            <div class="border-b border-gray-200 p-5">
                <p class="text-sm text-gray-700">
                    Semana:
                    <span class="font-medium">{{ $weekStart->format('d/m/Y') }}</span>
                    al
                    <span class="font-medium">{{ $weekEnd->format('d/m/Y') }}</span>
                </p>
            </div>

            <div class="overflow-x-auto p-5">
                <table class="vf-table min-w-full text-sm">
                    <thead class="border-b text-left text-gray-600">
                        <tr>
                            <th class="py-2 pr-4">Paciente</th>
                            <th class="py-2 pr-4">Estatus paciente</th>
                            <th class="py-2 pr-4">Actividad semanal</th>
                            <th class="py-2 pr-4">Paquete / Tratamientos</th>
                            <th class="py-2 pr-4">Avance</th>
                            <th class="py-2 pr-4">Pagado</th>
                            <th class="py-2 pr-4">Adeudo</th>
                            <th class="py-2 pr-4">Citas esta semana</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                    @forelse($packages as $pkg)
                        @php
                            $paid = (float)($paymentsByPackage[$pkg->id] ?? 0);
                            $due = max(0, (float)$pkg->package_total - $paid);
                            $patient = $pkg->patient;
                            $weekAppts = $appointmentsByPackage[$pkg->id] ?? collect();
                            $activity = $activityByPackage[$pkg->id] ?? 'Cita';
                        @endphp

                        <tr class="align-top">
                            <td class="py-4 pr-4">
                                <div class="min-w-[180px]">
                                    <p class="font-semibold text-gray-900">{{ $patient?->full_name ?? '—' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $patient?->phone ?? '—' }}</p>
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                @if($patient)
                                    <div class="min-w-[110px]">
                                        @if(($patient->packages_count ?? 0) > 1)
                                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                                Reactivado
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                                Nuevo
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[150px]">
                                    @if($activity === 'Cita + nueva asignación')
                                        <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-2 py-1 text-xs font-medium text-violet-700">
                                            Cita + nueva asignación
                                        </span>
                                    @elseif($activity === 'Nueva asignación')
                                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                            Nueva asignación
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                            Cita
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[260px] space-y-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $pkg->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Paquete #{{ $pkg->id }} •
                                            Asignado: {{ optional($pkg->started_on)->format('d/m/Y') ?? '—' }}
                                        </p>
                                        <div class="mt-2">
                                            <span class="inline-flex rounded-full border px-2 py-1 text-xs font-medium
                                                {{ $pkg->status === 'active'
                                                    ? 'border-green-200 bg-green-50 text-green-700'
                                                    : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                                                {{ ucfirst($pkg->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach($pkg->items as $item)
                                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-3 w-3 rounded"
                                                          style="background: {{ $item->treatment?->resolved_color_hex ?? '#111827' }}"></span>
                                                    <span class="font-medium text-gray-800">
                                                        {{ $item->treatment?->name ?? 'Tratamiento' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[280px] space-y-2">
                                    @foreach($pkg->items as $item)
                                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                                            <p class="mb-2 text-sm font-medium text-gray-800">
                                                {{ $item->treatment?->name ?? 'Tratamiento' }}
                                            </p>

                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="rounded-md border border-gray-200 bg-gray-50 p-2">
                                                    <p class="text-[11px] text-gray-500">Incluidas</p>
                                                    <p class="font-semibold text-gray-900">{{ $item->sessions_included }}</p>
                                                </div>

                                                <div class="rounded-md border border-gray-200 bg-gray-50 p-2">
                                                    <p class="text-[11px] text-gray-500">Usadas</p>
                                                    <p class="font-semibold text-gray-900">{{ $item->completed_sessions_count }}</p>
                                                </div>

                                                <div class="rounded-md border border-gray-200 bg-gray-50 p-2">
                                                    <p class="text-[11px] text-gray-500">Restantes</p>
                                                    <p class="font-semibold text-gray-900">{{ $item->remaining_sessions }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[110px]">
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                                        <p class="text-xs text-green-700">Pagado</p>
                                        <p class="mt-1 text-lg font-semibold text-green-800">
                                            ${{ number_format($paid, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[110px]">
                                    <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                                        <p class="text-xs text-red-700">Adeudo</p>
                                        <p class="mt-1 text-lg font-semibold text-red-800">
                                            ${{ number_format($due, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 pr-4">
                                <div class="min-w-[260px]">
                                    @if($weekAppts->isEmpty())
                                        <div class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500">
                                            Sin citas esta semana
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            @foreach($weekAppts as $a)
                                                @php
                                                    $badgeColor = $a->treatment?->resolved_color_hex ?? '#111827';
                                                    $label = $a->treatment?->name ?? 'Cita';
                                                    $time = $a->start_at->format('D d/m H:i');
                                                    $spec = $a->specialist?->name ?? '—';
                                                @endphp

                                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-2.5 w-2.5 rounded" style="background: {{ $badgeColor }}"></span>
                                                        <span class="font-medium text-gray-800">{{ $time }}</span>
                                                    </div>

                                                    <p class="mt-1 text-sm text-gray-700">{{ $label }}</p>
                                                    <p class="text-xs text-gray-500">{{ $spec }}</p>

                                                    <div class="mt-2">
                                                        <span class="inline-flex rounded-full border px-2 py-1 text-xs font-medium
                                                            {{ $a->status === 'confirmed' ? 'border-blue-200 bg-blue-50 text-blue-700' : '' }}
                                                            {{ $a->status === 'completed' ? 'border-green-200 bg-green-50 text-green-700' : '' }}
                                                            {{ in_array($a->status, ['cancelled', 'no_show']) ? 'border-red-200 bg-red-50 text-red-700' : '' }}
                                                            {{ $a->status === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700' : '' }}">
                                                            {{ ucfirst($a->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-500">
                                No hay actividad semanal para mostrar.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- VISTA IMPRESIÓN --}}
    <div class="print-only">
        <div class="print-header mb-4">
            <div class="print-header-left">
                <img src="{{ $printLogo }}" alt="VitalFit" class="print-logo">
            </div>

            <div class="print-header-right">
                <h1 class="print-title">Tabla semanal</h1>
                <p class="print-subtitle">
                    Semana: <strong>{{ $weekStart->format('d/m/Y') }}</strong> al <strong>{{ $weekEnd->format('d/m/Y') }}</strong>
                </p>

                @if($specialistId)
                    @php $selectedSpecialist = $specialists->firstWhere('id', $specialistId); @endphp
                    @if($selectedSpecialist)
                        <p class="text-sm">Especialista: <strong>{{ $selectedSpecialist->name }}</strong></p>
                    @endif
                @endif

                @if($treatmentId)
                    @php $selectedTreatment = $treatments->firstWhere('id', $treatmentId); @endphp
                    @if($selectedTreatment)
                        <p class="text-sm">Tratamiento: <strong>{{ $selectedTreatment->name }}</strong></p>
                    @endif
                @endif

                @if($status)
                    <p class="text-sm">Estatus: <strong>{{ $status }}</strong></p>
                @endif
            </div>
        </div>

        @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="print-summary-grid mb-4">
                <div class="print-summary-box">
                    <h2>Resumen semanal</h2>
                    <p><strong>Periodo:</strong> {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }}</p>
                    <p><strong>Ingresos de la semana:</strong> ${{ number_format($weeklyIncome ?? 0, 2) }}</p>
                    <p><strong>Canceladas / No-show:</strong> {{ $weeklyCancelledAppointments?->count() ?? 0 }}</p>

                    <div class="mt-2">
                        <p><strong>Top paquetes por ingresos</strong></p>
                        @forelse($weeklyTopPackagesByIncome ?? [] as $row)
                            <div>
                                {{ $row->name }} —
                                paquetes: {{ $row->packages_count }},
                                pagos: {{ $row->payments_count }},
                                ingreso: ${{ number_format($row->total_income, 2) }}
                            </div>
                        @empty
                            <div>Sin pagos registrados en esta semana.</div>
                        @endforelse
                    </div>

                    <div class="mt-2">
                        <p><strong>Citas cumplidas por tratamiento</strong></p>
                        @forelse($weeklyCompletedByTreatment ?? [] as $row)
                            <div>{{ $row->name }} — {{ $row->completed_count }}</div>
                        @empty
                            <div>Sin citas cumplidas en esta semana.</div>
                        @endforelse
                    </div>
                </div>

                <div class="print-summary-box">
                    <h2>Resumen mensual</h2>
                    <p><strong>Periodo:</strong> {{ $monthStart->format('d/m/Y') }} al {{ $monthEnd->format('d/m/Y') }}</p>
                    <p><strong>Ingresos del mes:</strong> ${{ number_format($monthlyIncome ?? 0, 2) }}</p>
                    <p><strong>Canceladas / No-show:</strong> {{ $monthlyCancelledAppointments?->count() ?? 0 }}</p>

                    <div class="mt-2">
                        <p><strong>Top paquetes por ingresos</strong></p>
                        @forelse($monthlyTopPackagesByIncome ?? [] as $row)
                            <div>
                                {{ $row->name }} —
                                paquetes: {{ $row->packages_count }},
                                pagos: {{ $row->payments_count }},
                                ingreso: ${{ number_format($row->total_income, 2) }}
                            </div>
                        @empty
                            <div>Sin pagos registrados en este mes.</div>
                        @endforelse
                    </div>

                    <div class="mt-2">
                        <p><strong>Citas cumplidas por tratamiento</strong></p>
                        @forelse($monthlyCompletedByTreatment ?? [] as $row)
                            <div>{{ $row->name }} — {{ $row->completed_count }}</div>
                        @empty
                            <div>Sin citas cumplidas en este mes.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <table class="print-table w-full text-xs">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Teléfono</th>
                    <th>Estatus paciente</th>
                    <th>Actividad</th>
                    <th>Paquete</th>
                    <th>Tratamientos</th>
                    <th>Sesión actual</th>
                    <th>Restantes</th>
                    <th>Pagado</th>
                    <th>Adeudo</th>
                    <th>Citas semana</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $pkg)
                    @php
                        $paid = (float)($paymentsByPackage[$pkg->id] ?? 0);
                        $due = max(0, (float)$pkg->package_total - $paid);
                        $patient = $pkg->patient;
                        $weekAppts = $appointmentsByPackage[$pkg->id] ?? collect();
                        $activity = $activityByPackage[$pkg->id] ?? 'Cita';

                        $allUsed = $pkg->items->sum(fn($item) => (int) $item->completed_sessions_count);
                        $allRemaining = $pkg->items->sum(fn($item) => (int) $item->remaining_sessions);
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $patient?->full_name ?? '—' }}</td>
                        <td>{{ $patient?->phone ?? '—' }}</td>
                        <td>
                            @if($patient)
                                {{ (($patient->packages_count ?? 0) > 1) ? 'Reactivado' : 'Nuevo' }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $activity }}</td>
                        <td>
                            {{ $pkg->name }}<br>
                            <span class="print-muted">#{{ $pkg->id }} / {{ optional($pkg->started_on)->format('d/m/Y') ?? '—' }}</span>
                        </td>
                        <td>
                            @foreach($pkg->items as $item)
                                <div>• {{ $item->treatment?->name ?? 'Tratamiento' }}</div>
                            @endforeach
                        </td>
                        <td>{{ $allUsed }}</td>
                        <td>{{ $allRemaining }}</td>
                        <td>${{ number_format($paid, 2) }}</td>
                        <td>${{ number_format($due, 2) }}</td>
                        <td>
                            @if($weekAppts->isEmpty())
                                —
                            @else
                                @foreach($weekAppts as $a)
                                    <div class="mb-1">
                                        {{ $a->start_at->format('d/m H:i') }} -
                                        {{ $a->treatment?->name ?? 'Cita' }}
                                        ({{ $a->status }})
                                    </div>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">No hay actividad semanal para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

<style>
    .screen-only {
        display: block;
    }

    .print-only {
        display: none;
    }

    :root {
        --vf-primary: #6b7a57;
        --vf-primary-soft: #eef3e8;
        --vf-secondary: #c8a97e;
        --vf-secondary-soft: #f7efe5;
        --vf-text: #2f2f2f;
        --vf-border: #cfc7bb;
    }

    .print-header {
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 2px solid var(--vf-primary);
        padding-bottom: 10px;
        margin-bottom: 14px;
    }

    .print-header-left {
        flex: 0 0 auto;
    }

    .print-header-right {
        flex: 1 1 auto;
    }

    .print-logo {
        width: 72px;
        height: auto;
        object-fit: contain;
    }

    .print-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--vf-primary);
        margin: 0 0 2px 0;
    }

    .print-subtitle {
        color: var(--vf-text);
        margin: 0;
    }

    .print-muted {
        color: #666;
    }

    .print-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }

    .print-summary-box {
        border: 1px solid var(--vf-border);
        background: linear-gradient(to bottom, var(--vf-primary-soft), #ffffff 20%);
        padding: 10px;
        font-size: 10px;
        line-height: 1.35;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .print-summary-box h2 {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--vf-primary);
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .print-table th,
    .print-table td {
        border: 1px solid var(--vf-border);
        padding: 6px;
        vertical-align: top;
        text-align: left;
        word-break: break-word;
        color: var(--vf-text);
    }

    .print-table th {
        background: var(--vf-secondary-soft);
        color: var(--vf-primary);
        font-weight: 700;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        html,
        body {
            background: #fff !important;
            color: var(--vf-text) !important;
            font-size: 10px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .screen-only,
        .no-print,
        nav,
        aside,
        header,
        footer,
        button,
        form {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        .print-header {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            border-bottom: 2px solid var(--vf-primary) !important;
            padding-bottom: 8px !important;
            margin-bottom: 12px !important;
        }

        .print-logo {
            width: 64px !important;
            height: auto !important;
        }

        .print-title {
            font-size: 18px !important;
            color: var(--vf-primary) !important;
            margin: 0 0 2px 0 !important;
        }

        .print-summary-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
        }

        .print-summary-box {
            border: 1px solid var(--vf-border) !important;
            background: linear-gradient(to bottom, var(--vf-primary-soft), #ffffff 20%) !important;
            padding: 6px !important;
            font-size: 9px !important;
            line-height: 1.25 !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            color: var(--vf-text) !important;
        }

        .print-summary-box h2 {
            font-size: 11px !important;
            font-weight: 700 !important;
            margin-bottom: 4px !important;
            color: var(--vf-primary) !important;
        }

        .print-table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
        }

        .print-table th,
        .print-table td {
            border: 1px solid var(--vf-border) !important;
            padding: 4px !important;
            font-size: 8px !important;
            line-height: 1.15 !important;
            vertical-align: top !important;
            color: var(--vf-text) !important;
        }

        .print-table th {
            background: var(--vf-secondary-soft) !important;
            color: var(--vf-primary) !important;
        }

        .print-table tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .vf-card,
        .rounded-lg,
        .rounded-md,
        .shadow,
        .shadow-sm,
        .shadow-md,
        .shadow-lg {
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        a {
            color: var(--vf-text) !important;
            text-decoration: none !important;
        }
    }
</style>