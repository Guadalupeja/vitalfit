@extends('layouts.app')

@section('title', ' | Tabla semanal')
@section('page_title', 'Tabla semanal')
@section('page_subtitle', 'Actividad semanal por paciente: citas, nuevas asignaciones, pagos y avance de paquetes.')

@section('page_actions')
    <div class="flex w-full flex-col gap-2">
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
                <button type="button" onclick="window.print()" class="vf-btn-secondary">Imprimir</button>
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
    @if(auth()->check() && auth()->user()->role === 'admin')
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
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
                    <h4 class="font-medium text-gray-900">Top tratamientos por ingresos</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="vf-table min-w-full text-sm">
                            <thead class="border-b text-left text-gray-600">
                                <tr>
                                    <th class="py-2 pr-4">Tratamiento</th>
                                    <th class="py-2 pr-4">Pagos</th>
                                    <th class="py-2 pr-4">Ingreso</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($weeklyTopTreatmentsByIncome ?? [] as $row)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                        <td class="py-2 pr-4 text-gray-600">{{ $row->payments_count }}</td>
                                        <td class="py-2 pr-4 font-medium text-gray-900">${{ number_format($row->total_income, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">Sin pagos registrados en esta semana.</td>
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
                    <h4 class="font-medium text-gray-900">Top tratamientos por ingresos</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="vf-table min-w-full text-sm">
                            <thead class="border-b text-left text-gray-600">
                                <tr>
                                    <th class="py-2 pr-4">Tratamiento</th>
                                    <th class="py-2 pr-4">Pagos</th>
                                    <th class="py-2 pr-4">Ingreso</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($monthlyTopTreatmentsByIncome ?? [] as $row)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-800">{{ $row->name }}</td>
                                        <td class="py-2 pr-4 text-gray-600">{{ $row->payments_count }}</td>
                                        <td class="py-2 pr-4 font-medium text-gray-900">${{ number_format($row->total_income, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">Sin pagos registrados en este mes.</td>
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
@endsection