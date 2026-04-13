@extends('layouts.app')

@section('title', ' | Tabla semanal')
@section('page_title', 'Tabla semanal')
@section('page_subtitle', 'Resumen semanal por paquete activo: sesiones, pagos y citas programadas.')

@section('page_actions')
    <div class="flex flex-col gap-2 w-full">
        <form method="GET" action="{{ route('tabla_semanal.index') }}" class="flex flex-col lg:flex-row gap-2 lg:items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600">Semana (lunes)</label>
                <input type="date" name="week_start"
                       value="{{ $weekStart->format('Y-m-d') }}"
                       class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Especialista</label>
                <select name="specialist_id"
                        class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
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
                <select name="treatment_id"
                        class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
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
                <select name="status"
                        class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">Todos</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmada</option>
                    <option value="completed" @selected($status === 'completed')>Realizada</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelada</option>
                    <option value="no_show" @selected($status === 'no_show')>No show</option>
                    <option value="pending" @selected($status === 'pending')>Pendiente</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Filtrar
                </button>

                <a href="{{ route('tabla_semanal.index') }}"
                   class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                    Limpiar
                </a>

                <button type="button"
                        onclick="window.print()"
                        class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                    Imprimir
                </button>
            </div>
        </form>

        <div>
            <a href="{{ route('agenda.index') }}"
               class="inline-flex rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                Ir a Agenda
            </a>
        </div>
    </div>
@endsection

@section('content')

    {{-- =========================
         BLOQUES DE ESTADÍSTICAS (SOLO ADMIN)
       ========================= --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            {{-- Resumen semanal --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="font-semibold text-gray-900">
                    Resumen semanal ({{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m') }})
                </h3>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Ingresos de la semana</p>
                        <p class="text-lg font-semibold text-gray-900">
                            ${{ number_format($weeklyIncome ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Canceladas / No-show</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $weeklyCancelledAppointments?->count() ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Top tratamientos por ingresos</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                        <td class="py-2 pr-4 font-medium text-gray-900">
                                            ${{ number_format($row->total_income, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">
                                            Sin pagos registrados en esta semana.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Citas cumplidas (completed) por tratamiento</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                        <td colspan="2" class="py-6 text-center text-gray-500">
                                            Sin citas cumplidas en esta semana.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Canceladas / No-show (detalle)</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium border
                                                {{ $a->status === 'no_show'
                                                    ? 'bg-red-50 text-red-700 border-red-200'
                                                    : 'bg-orange-50 text-orange-700 border-orange-200' }}">
                                                {{ $a->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-500">
                                            Sin cancelaciones/no-show en esta semana.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Resumen mensual --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="font-semibold text-gray-900">
                    Resumen mensual ({{ $monthStart->format('d/m') }} - {{ $monthEnd->format('d/m') }})
                </h3>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Ingresos del mes</p>
                        <p class="text-lg font-semibold text-gray-900">
                            ${{ number_format($monthlyIncome ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Canceladas / No-show</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $monthlyCancelledAppointments?->count() ?? 0 }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Top tratamientos por ingresos</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                        <td class="py-2 pr-4 font-medium text-gray-900">
                                            ${{ number_format($row->total_income, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">
                                            Sin pagos registrados en este mes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Citas cumplidas (completed) por tratamiento</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                        <td colspan="2" class="py-6 text-center text-gray-500">
                                            Sin citas cumplidas en este mes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium text-gray-900">Canceladas / No-show (detalle)</h4>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
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
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium border
                                                {{ $a->status === 'no_show'
                                                    ? 'bg-red-50 text-red-700 border-red-200'
                                                    : 'bg-orange-50 text-orange-700 border-orange-200' }}">
                                                {{ $a->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-500">
                                            Sin cancelaciones/no-show en este mes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =========================
         TU TABLA EXISTENTE (VISIBLE PARA TODOS)
       ========================= --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <p class="text-sm text-gray-700">
                Semana: <span class="font-medium">{{ $weekStart->format('d/m/Y') }}</span>
                al <span class="font-medium">{{ $weekEnd->format('d/m/Y') }}</span>
            </p>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-600 border-b">
                    <tr>
                        <th class="py-2 pr-4">Paciente</th>
                        <th class="py-2 pr-4">Paquete / Tratamiento</th>
                        <th class="py-2 pr-4">Sesión</th>
                        <th class="py-2 pr-4">Restan</th>
                        <th class="py-2 pr-4">Pagado</th>
                        <th class="py-2 pr-4">Adeudo</th>
                        <th class="py-2 pr-4">Citas esta semana</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($packages as $pkg)
                    @php
                        $paid = (float)($pkg->total_paid ?? 0);
                        $due = max(0, (float)$pkg->package_total - $paid);

                        $completed = (int)($pkg->completed_sessions ?? 0);
                        $purchased = (int)($pkg->sessions_purchased ?? 0);

                        $remaining = max(0, $purchased - $completed);
                        $sessionCurrent = $purchased === 0 ? 0 : ($remaining > 0 ? $completed + 1 : $purchased);

                        $weekAppts = $appointmentsByPackage[$pkg->id] ?? collect();
                        $patient = $pkg->patient;
                        $treat = $pkg->treatment;
                    @endphp

                    <tr>
                        <td class="py-3 pr-4">
                            <p class="font-medium">{{ $patient?->full_name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $patient?->phone ?? '—' }}</p>
                        </td>

                        <td class="py-3 pr-4">
                            @if($treat)
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded" style="background: {{ $treat->color_hex }}"></span>
                                    <span class="font-medium">{{ $treat->name }}</span>
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    Paquete #{{ $pkg->id }} • {{ $purchased }} sesiones • Estatus: {{ $pkg->status }}
                                </div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>

                        <td class="py-3 pr-4">
                            {{ $sessionCurrent }}
                            @if($purchased > 0)
                                <span class="text-gray-500">/ {{ $purchased }}</span>
                            @endif
                        </td>

                        <td class="py-3 pr-4">{{ $remaining }}</td>

                        <td class="py-3 pr-4">${{ number_format($paid, 2) }}</td>
                        <td class="py-3 pr-4">${{ number_format($due, 2) }}</td>

                        <td class="py-3 pr-4">
                            @if($weekAppts->isEmpty())
                                <span class="text-gray-500">—</span>
                            @else
                                <div class="space-y-1">
                                    @foreach($weekAppts as $a)
                                        @php
                                            $badgeColor = $a->treatment?->color_hex ?? ($treat?->color_hex ?? '#111827');
                                            $label = $a->treatment?->name ?? ($treat?->name ?? 'Cita');
                                            $time = $a->start_at->format('D d/m H:i');
                                            $spec = $a->specialist?->name ?? '—';
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded" style="background: {{ $badgeColor }}"></span>
                                            <span class="text-gray-700">{{ $time }}</span>
                                            <span class="text-gray-500">— {{ $label }}</span>
                                            <span class="text-gray-400">({{ $spec }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-500">
                            No hay paquetes activos.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
