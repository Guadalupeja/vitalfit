@extends('layouts.app')

@section('title', ' | Pacientes')
@section('page_title', 'Pacientes')
@section('page_subtitle', 'Listado por paciente con paquetes activos: tratamiento, sesión, pagado y adeudo.')

@section('page_actions')
    <a href="{{ route('pacientes.create') }}"
       class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
        + Nuevo paciente
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $patients->total() }}</span>
            </p>

            <form method="GET" action="{{ route('pacientes.index') }}" class="flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Buscar por nombre o teléfono..."
                    class="w-full lg:w-80 rounded-md border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900"
                >

                <button class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Buscar
                </button>

                @if(!empty($q))
                    <a href="{{ route('pacientes.index') }}"
                    class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-600 border-b">
                    <tr>
                        <th class="py-2 pr-4">Paciente</th>
                        <th class="py-2 pr-4">Paquetes activos</th>
                        <th class="py-2 pr-4">Última cita</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($patients as $p)
                    <tr>
                        <td class="py-3 pr-4 align-top">
                            <p class="font-medium">{{ $p->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $p->phone ?? '—' }}</p>
                        </td>

                        <td class="py-3 pr-4 align-top">
                            @if(($p->packages ?? collect())->isEmpty())
                                <span class="text-gray-500">Sin paquetes activos</span>
                                <div class="mt-2 text-xs text-gray-500">
                                    Puedes agregar paquetes desde “Editar”.
                                </div>
                            @else
                                <div class="space-y-3 min-w-[520px]">
                                    @foreach($p->packages as $pkg)
                                        @php
                                            $t = $pkg->treatment;

                                            $paid = (float)($pkg->total_paid ?? 0);
                                            $due = max(0, (float)$pkg->package_total - $paid);

                                            $completed = (int)($pkg->completed_sessions ?? 0);
                                            $purchased = (int)($pkg->sessions_purchased ?? 0);

                                            $remaining = max(0, $purchased - $completed);
                                            $sessionCurrent = $purchased === 0 ? 0 : ($remaining > 0 ? $completed + 1 : $purchased);
                                        @endphp

                                        <div class="rounded-lg border border-gray-200 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-3 w-3 rounded"
                                                          style="background: {{ $t?->color_hex ?? '#111827' }}"></span>
                                                    <div>
                                                        <p class="font-medium text-gray-900">
                                                            {{ $t?->name ?? 'Paquete' }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Paquete #{{ $pkg->id }} • {{ $purchased }} sesiones • Estatus: {{ $pkg->status }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                                <div class="rounded-md bg-gray-50 border border-gray-100 p-2">
                                                    <p class="text-xs text-gray-500">Sesión actual</p>
                                                    <p class="font-medium text-gray-900">
                                                        {{ $sessionCurrent }}
                                                        @if($purchased > 0)
                                                            <span class="text-gray-500">/ {{ $purchased }}</span>
                                                        @endif
                                                    </p>
                                                </div>

                                                <div class="rounded-md bg-gray-50 border border-gray-100 p-2">
                                                    <p class="text-xs text-gray-500">Restantes</p>
                                                    <p class="font-medium text-gray-900">{{ $remaining }}</p>
                                                </div>

                                                <div class="rounded-md bg-gray-50 border border-gray-100 p-2">
                                                    <p class="text-xs text-gray-500">Pagado</p>
                                                    <p class="font-medium text-gray-900">${{ number_format($paid, 2) }}</p>
                                                </div>

                                                <div class="rounded-md bg-gray-50 border border-gray-100 p-2">
                                                    <p class="text-xs text-gray-500">Adeudo</p>
                                                    <p class="font-medium text-gray-900">${{ number_format($due, 2) }}</p>
                                                </div>
                                            </div>

                                            {{-- Si quisieras, aquí podemos agregar accesos rápidos:
                                                 - “Ver pagos” filtrado por paquete
                                                 - “Agendar” preseleccionando paciente y paquete
                                            --}}

                                            <div class="mt-3 flex flex-wrap gap-2">
    <a href="{{ route('agenda.index', ['patient_id' => $p->id, 'patient_treatment_id' => $pkg->id]) }}"
       class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-xs font-medium text-white hover:bg-gray-800">
        Agendar
    </a>

    <a href="{{ route('pagos.create', ['patient_id' => $p->id, 'patient_treatment_id' => $pkg->id]) }}"
       class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-900 hover:bg-gray-50">
        Registrar pago
    </a>
</div>

                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>


<td class="py-3 pr-4 align-top">
    @php
        $lastAppointment = $p->appointments->first();
    @endphp

    @if($lastAppointment)
        <div class="text-sm">
            <p class="font-medium text-gray-900">
                {{ $lastAppointment->start_at?->format('d/m/Y H:i') }}
            </p>
            <p class="text-gray-600">
                {{ $lastAppointment->treatment?->name ?? 'Sin tratamiento' }}
            </p>
            <p class="text-xs text-gray-500">
                {{ $lastAppointment->specialist?->name ?? 'Sin especialista' }}
            </p>
            <p class="mt-1">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium border
                    {{ $lastAppointment->status === 'completed' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                    {{ $lastAppointment->status === 'confirmed' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                    {{ $lastAppointment->status === 'cancelled' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                    {{ $lastAppointment->status === 'no_show' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                    {{ $lastAppointment->status }}
                </span>
            </p>
        </div>
    @else
        <span class="text-gray-500">Sin citas registradas</span>
    @endif
</td>






                        <td class="py-3 pr-4 align-top">
                            @if($p->active)
                                <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 border border-green-200">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 border border-gray-200">
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <td class="py-3 pr-4 whitespace-nowrap align-top">
                            <a href="{{ route('pacientes.edit', $p) }}" class="text-gray-900 font-medium hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('pacientes.destroy', $p) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar este paciente?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-3 text-red-600 font-medium hover:underline" type="submit">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            No hay pacientes. Crea el primero.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
            <div class="p-5 border-t border-gray-200">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

    <p class="text-xs text-gray-500 mt-3">
        Nota: La información de sesiones/pagos se calcula por cada paquete activo (tratamiento) del paciente.
        Sesiones realizadas = citas con estatus “completed” asociadas al paquete.
    </p>
@endsection
