@extends('layouts.app')

@section('title', ' | Horarios')
@section('page_title', 'Horarios de nutrición')
@section('page_subtitle', 'Configura los días y horarios en que Marisol atiende nutrición por sucursal.')

@section('page_actions')
    <a href="{{ route('horarios.create') }}" class="vf-btn-primary">
        + Nuevo horario
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="vf-card">
        <div class="border-b border-gray-200 p-5">
            <h3 class="text-lg font-semibold text-gray-900">Horarios configurados</h3>
            <p class="mt-1 text-sm text-gray-500">
                Estos horarios serán usados por el chatbot y por la agenda para validar disponibilidad de nutrición.
            </p>
        </div>

        <div class="p-5">
            @if($schedules->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                    <p class="text-gray-600">Aún no hay horarios de nutrición configurados.</p>

                    <a href="{{ route('horarios.create') }}" class="vf-btn-primary mt-4 inline-flex">
                        Crear primer horario
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Sucursal</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Día</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Horario</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Servicio</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Especialista</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Estatus</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td class="px-4 py-3">{{ $schedule->branch?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $schedule->weekday_name }}</td>
                                    <td class="px-4 py-3">
                                        {{ $schedule->start_time_short }} - {{ $schedule->end_time_short }}
                                    </td>
                                    <td class="px-4 py-3">{{ $schedule->service_type_label }}</td>
                                    <td class="px-4 py-3">{{ $schedule->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($schedule->active)
                                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                                                Activo
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('horarios.edit', $schedule) }}"
                                               class="font-semibold text-[#6b7a57] hover:underline">
                                                Editar
                                            </a>

                                            <form action="{{ route('horarios.destroy', $schedule) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar este horario?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="font-semibold text-red-600 hover:underline">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                @if($schedule->notes)
                                    <tr class="bg-gray-50">
                                        <td colspan="7" class="px-4 py-2 text-xs text-gray-500">
                                            <strong>Notas:</strong> {{ $schedule->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection