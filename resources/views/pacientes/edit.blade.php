@extends('layouts.app')

@section('title', ' | Editar paciente')
@section('page_title', 'Editar paciente')
@section('page_subtitle', 'Actualizar información del paciente.')

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('pacientes.update', $paciente) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('pacientes._form', ['paciente' => $paciente, 'treatments' => $treatments])

            <div class="flex items-center gap-2">
                <a href="{{ route('pacientes.index') }}"
                   class="rounded-md border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                    Volver
                </a>

                <button type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>



<div class="mt-8 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-semibold">Paquetes del paciente</p>
            <p class="text-sm text-gray-500">Puede tener varios activos a la vez.</p>
        </div>
    </div>

    {{-- Crear nuevo paquete --}}
    <form method="POST" action="{{ route('pacientes.paquetes.store', $paciente) }}" class="mt-5 grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf

        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600">Tratamiento</label>
            <select name="treatment_id" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                @foreach($treatments as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->duration_minutes }} min)</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Sesiones</label>
            <input type="number" name="sessions_purchased" min="1" value="5"
                   class="mt-1 w-full rounded-md border-gray-300 text-sm">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Total ($)</label>
            <input type="number" step="0.01" name="package_total" value="0"
                   class="mt-1 w-full rounded-md border-gray-300 text-sm">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Estatus</label>
            <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                <option value="active">Activo</option>
                <option value="paused">Pausado</option>
                <option value="finished">Finalizado</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>

        <div class="flex items-end">
            <button class="w-full rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Agregar
            </button>
        </div>
    </form>

    {{-- Listado de paquetes --}}
    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-gray-600 border-b">
                <tr>
                    <th class="py-2 pr-4">Tratamiento</th>
                    <th class="py-2 pr-4">Sesiones</th>
                    <th class="py-2 pr-4">Total</th>
                    <th class="py-2 pr-4">Estatus</th>
                    <th class="py-2 pr-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse($packages as $pkg)
                <tr>
                    <td class="py-3 pr-4">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded" style="background: {{ $pkg->treatment->color_hex }}"></span>
                            <span class="font-medium">{{ $pkg->treatment->name }}</span>
                        </span>
                    </td>

                    <td class="py-3 pr-4">{{ $pkg->sessions_purchased }}</td>
                    <td class="py-3 pr-4">${{ number_format((float)$pkg->package_total, 2) }}</td>

                    <td class="py-3 pr-4">
                        <span class="inline-flex rounded-full border px-2 py-1 text-xs
                            {{ $pkg->status === 'active' ? 'border-green-200 bg-green-50 text-green-700' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                            {{ $pkg->status }}
                        </span>
                    </td>

                    <td class="py-3 pr-4 whitespace-nowrap">
                        {{-- edición rápida inline --}}
                        <form method="POST" action="{{ route('paquetes.update', $pkg) }}" class="inline-flex items-center gap-2">
                            @csrf
                            @method('PUT')

                            <input type="number" name="sessions_purchased" min="1" value="{{ $pkg->sessions_purchased }}"
                                   class="w-20 rounded-md border-gray-300 text-sm">
                            <input type="number" step="0.01" name="package_total" value="{{ $pkg->package_total }}"
                                   class="w-28 rounded-md border-gray-300 text-sm">
                            <select name="status" class="rounded-md border-gray-300 text-sm">
                                <option value="active" @selected($pkg->status==='active')>active</option>
                                <option value="paused" @selected($pkg->status==='paused')>paused</option>
                                <option value="finished" @selected($pkg->status==='finished')>finished</option>
                                <option value="cancelled" @selected($pkg->status==='cancelled')>cancelled</option>
                            </select>

                            <button class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                                Guardar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('paquetes.destroy', $pkg) }}" class="inline"
                              onsubmit="return confirm('¿Eliminar este paquete?')">
                            @csrf
                            @method('DELETE')
                            <button class="ml-2 text-red-600 font-medium hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">Aún no hay paquetes.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>



{{-- =========================
     HISTORIAL POR PAQUETE
   ========================= --}}
<div class="mt-8 space-y-6">
    @forelse($packages as $pkg)
        @php
            $t = $pkg->treatment;
            $paid = (float)($pkg->total_paid ?? 0);
            $due = max(0, (float)$pkg->package_total - $paid);

            $completed = (int)($pkg->completed_sessions ?? 0);
            $purchased = (int)($pkg->sessions_purchased ?? 0);
            $remaining = max(0, $purchased - $completed);
            $sessionCurrent = $purchased === 0 ? 0 : ($remaining > 0 ? $completed + 1 : $purchased);
        @endphp

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded" style="background: {{ $t?->color_hex ?? '#111827' }}"></span>
                        <p class="font-semibold text-gray-900">{{ $t?->name ?? 'Paquete' }}</p>
                        <span class="text-xs text-gray-500">#{{ $pkg->id }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        Sesión: <span class="font-medium">{{ $sessionCurrent }}/{{ $purchased }}</span> •
                        Restantes: <span class="font-medium">{{ $remaining }}</span>
                    </p>
                    <p class="text-sm text-gray-600">
                        Pagado: <span class="font-medium">${{ number_format($paid, 2) }}</span> •
                        Adeudo: <span class="font-medium">${{ number_format($due, 2) }}</span>
                    </p>
                </div>

                <div class="text-right">
                    <span class="inline-flex rounded-full border px-2 py-1 text-xs
                        {{ $pkg->status === 'active' ? 'border-green-200 bg-green-50 text-green-700' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                        {{ $pkg->status }}
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- PAGOS --}}
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">Pagos del paquete</p>
                        <a href="{{ route('pagos.create', ['patient_id' => $paciente->id, 'patient_treatment_id' => $pkg->id]) }}"
                           class="text-sm font-medium text-gray-900 hover:underline">
                            + Registrar pago
                        </a>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
                                <tr>
                                    <th class="py-2 pr-4">Fecha</th>
                                    <th class="py-2 pr-4">Monto</th>
                                    <th class="py-2 pr-4">Método</th>
                                    <th class="py-2 pr-4">Nota</th>
                                    <th class="py-2 pr-4">Comprobante</th>
                                    <th class="py-2 pr-4">Registró</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @php $labels = ['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta']; @endphp

                                @forelse($pkg->payments as $pay)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-700">{{ $pay->paid_at?->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 pr-4 font-medium text-gray-900">${{ number_format((float)$pay->amount, 2) }}</td>
                                        <td class="py-2 pr-4 text-gray-700">{{ $labels[$pay->method] ?? $pay->method }}</td>
                                        <td class="py-2 pr-4 text-gray-600">{{ $pay->note ?? '—' }}</td>
                                        <td class="py-2 pr-4 text-gray-600">
                                        @if($pay->receipt_path)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($pay->receipt_path) }}"
                                            target="_blank"
                                            class="font-medium text-gray-900 hover:underline">
                                                Ver
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                        <td class="py-2 pr-4 text-gray-600">{{ $pay->creator?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-gray-500">Sin pagos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CITAS --}}
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">Citas / Sesiones</p>
                        <a href="{{ route('agenda.index', ['patient_id' => $paciente->id, 'patient_treatment_id' => $pkg->id]) }}"
                           class="text-sm font-medium text-gray-900 hover:underline">
                            + Agendar
                        </a>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
                                <tr>
                                    <th class="py-2 pr-4">Fecha</th>
                                    <th class="py-2 pr-4">Especialista</th>
                                    <th class="py-2 pr-4">Estatus</th>
                                    <th class="py-2 pr-4">Notas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($pkg->appointments as $a)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-700">{{ $a->start_at?->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 pr-4 text-gray-700">{{ $a->specialist?->name ?? '—' }}</td>
                                        <td class="py-2 pr-4">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium border
                                                {{ $a->status === 'completed' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                                {{ $a->status === 'confirmed' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                                {{ $a->status === 'cancelled' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                                {{ $a->status === 'no_show' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                                {{ $a->status }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-4 text-gray-600">{{ $a->notes ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-500">Sin citas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-sm text-gray-500">
            Aún no hay paquetes para este paciente.
        </div>
    @endforelse
</div>

</div>




@endsection
