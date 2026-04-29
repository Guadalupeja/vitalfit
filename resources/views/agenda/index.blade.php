@extends('layouts.app')

@section('title', ' | Agenda')
@section('page_title', 'Agenda')
@section('page_subtitle', 'Calendario día/semana/mes con colores por tratamiento. Todos pueden ver; se guarda quién agendó.')

@section('page_actions')
    <button type="button"
            id="btnOpenNewAppointment"
            class="vf-btn-primary">
        Agendar cita
    </button>
@endsection

@section('content')
    {{-- Datos para JS --}}
    <div id="agenda-data"
         data-events-url="{{ route('agenda.events') }}"
         data-store-url="{{ route('agenda.appointments.store') }}"
         data-patient-packages-url="{{ route('api.pacientes.paquetes_v2', ['patient' => 0]) }}"
         data-package-items-url="{{ route('api.paquetes_paciente.items', ['patientPackage' => 0]) }}"
         data-prefill-patient-id="{{ request('patient_id') }}"
         data-prefill-package-id="{{ request('patient_package_id') }}"
         data-available-specialists-url="{{ route('agenda.available_specialists') }}"
         data-csrf="{{ csrf_token() }}"
         class="hidden"></div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <div class="lg:col-span-3">
            <div class="vf-card">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <p class="font-semibold">Calendario</p>
                    <p class="text-sm text-gray-500">Tip: click para crear, arrastra para mover</p>
                </div>

                <div class="p-5">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-1">
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <p class="font-semibold mb-3">Leyenda</p>

    <div class="space-y-2 text-sm">
        @forelse($treatmentTypes as $type)
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded" style="background: {{ $type->color_hex }}"></span>
                <span>{{ $type->name }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No hay tipos de tratamiento configurados.</p>
        @endforelse

        <div class="flex items-center gap-2 pt-2 border-t border-gray-100 mt-2">
            <span class="h-3 w-3 rounded bg-red-600"></span>
            <span>Cancelada / No asistió</span>
        </div>
    </div>
</div>

            <div class="vf-card p-5">
                <p class="mb-2 font-semibold">Reglas</p>
                <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600">
                    <li>Se evita empalme por especialista.</li>
                    <li>Se registra quién agendó.</li>
                    <li>Solo se pueden usar paquetes activos.</li>
                    <li>Solo se muestran tratamientos del paquete con sesiones disponibles.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Modal --}}
<div
    id="appointmentModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
>
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
            <div>
                <h2 id="modalTitle" class="text-xl font-semibold text-gray-900">Nueva cita</h2>
                <p id="modalSubtitle" class="mt-1 text-sm text-gray-500">Se guardará el nombre de quien agendó.</p>
            </div>

            <button
                type="button"
                data-close="1"
                class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
            >
                ✕
            </button>
        </div>

        <form id="appointmentForm" class="space-y-5 px-6 py-5">
            @csrf

            <input type="hidden" id="appointment_id" name="appointment_id">

            <div
                id="modalError"
                class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            ></div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="patient_id" class="block text-sm font-medium text-gray-700">
                        Paciente
                    </label>
                    <select id="patient_id" name="patient_id" class="vf-input mt-1" required>
                        <option value="">— Selecciona —</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="patient_package_id" class="block text-sm font-medium text-gray-700">
                        Paquete del paciente
                    </label>
                    <select id="patient_package_id" name="patient_package_id" class="vf-input mt-1" required>
                        <option value="">— Selecciona paciente primero —</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecciona un paquete activo del paciente.
                    </p>
                </div>

                <div>
                    <label for="patient_package_item_id" class="block text-sm font-medium text-gray-700">
                        Tratamiento del paquete
                    </label>
                    <select id="patient_package_item_id" name="patient_package_item_id" class="vf-input mt-1" required>
                        <option value="">— Selecciona paquete primero —</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Solo se muestran tratamientos con sesiones disponibles.
                    </p>
                </div>

                <div class="hidden">
                    <label for="treatment_id" class="block text-sm font-medium text-gray-700">
                        Tratamiento
                    </label>
                    <select id="treatment_id" name="treatment_id" class="vf-input mt-1">
                        <option value="">— Automático según el paquete —</option>
                        @foreach($treatments as $t)
                            <option value="{{ $t->id }}" data-duration="{{ $t->duration_minutes }}">
                                {{ $t->name }}
                                —
                                {{ $t->resolved_type_name ?? ($t->type->name ?? $t->category ?? 'Sin tipo') }}
                                ({{ $t->duration_minutes }} min)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="specialist_id" class="block text-sm font-medium text-gray-700">
                        Especialista
                    </label>
                    <select
                        id="specialist_id"
                        name="specialist_id"
                        class="vf-input mt-1"
                        required
                        data-current-user-id="{{ $currentUserId }}"
                    >
                        <option value="">— Selecciona —</option>
                        @foreach($specialists as $specialist)
                            <option value="{{ $specialist->id }}">
                                {{ $specialist->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Estatus
                    </label>
                    <select id="status" name="status" class="vf-input mt-1" required>
                        <option value="confirmed">Confirmada</option>
                        <option value="pending">Pendiente</option>
                        <option value="completed">Realizada</option>
                        <option value="cancelled">Cancelada</option>
                        <option value="no_show">No show</option>
                    </select>
                </div>

                <div>
                    <label for="start_at" class="block text-sm font-medium text-gray-700">
                        Inicio
                    </label>
                    <input
                        id="start_at"
                        name="start_at"
                        type="datetime-local"
                        class="vf-input mt-1"
                        required
                    >
                </div>

                <div>
                    <label for="end_at" class="block text-sm font-medium text-gray-700">
                        Fin
                    </label>
                    <input
                        id="end_at"
                        name="end_at"
                        type="datetime-local"
                        class="vf-input mt-1"
                        required
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Notas
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="4"
                        class="vf-input mt-1"
                    ></textarea>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        id="btnCancelAppointment"
                        class="hidden rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50"
                    >
                        Cancelar cita
                    </button>

                    <button
                        type="button"
                        id="btnNoShow"
                        class="hidden rounded-lg border border-orange-200 px-4 py-2 text-sm font-medium text-orange-700 transition hover:bg-orange-50"
                    >
                        Marcar no-show
                    </button>

                    <button
                        type="button"
                        id="btnCompleteAppointment"
                        class="hidden rounded-lg border border-green-200 px-4 py-2 text-sm font-medium text-green-700 transition hover:bg-green-50"
                    >
                        Marcar realizada
                    </button>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        data-close="1"
                        class="vf-btn-secondary"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="vf-btn-primary"
                    >
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection