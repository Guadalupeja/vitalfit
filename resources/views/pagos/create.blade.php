@extends('layouts.app')

@section('title', ' | Registrar pago')
@section('page_title', 'Registrar pago')
@section('page_subtitle', 'Registrar abono y actualizar saldo del paciente.')

@section('content')
    <div class="vf-card p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-medium">Hay errores en el formulario:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('pagos.store') }}"
              enctype="multipart/form-data"
              class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Paciente</label>
                <select name="patient_id" id="patient_id" class="vf-input mt-1" required>
                    <option value="">— Selecciona —</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                            {{ $patient->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Paquete del paciente</label>
                <select name="patient_package_id" id="patient_package_id" class="vf-input mt-1" required>
                    <option value="">— Selecciona paciente primero —</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Solo se muestran paquetes activos disponibles del paciente.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha y hora</label>
                <input type="datetime-local"
                       name="paid_at"
                       value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                       class="vf-input mt-1"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Monto</label>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       name="amount"
                       value="{{ old('amount') }}"
                       class="vf-input mt-1"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Método</label>
                <select name="method" class="vf-input mt-1" required>
                    <option value="">— Selecciona —</option>
                    <option value="cash" @selected(old('method') === 'cash')>Efectivo</option>
                    <option value="transfer" @selected(old('method') === 'transfer')>Transferencia</option>
                    <option value="card" @selected(old('method') === 'card')>Tarjeta</option>
                    <option value="other" @selected(old('method') === 'other')>Otro</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Referencia (opcional)</label>
                <input type="text"
                       name="reference"
                       value="{{ old('reference') }}"
                       class="vf-input mt-1">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
                <textarea name="notes" rows="3" class="vf-input mt-1">{{ old('notes') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Comprobante (opcional)</label>
                <input type="file" name="receipt" class="vf-input mt-1">
            </div>

            <div class="md:col-span-2 flex items-center gap-2 pt-2">
                <a href="{{ route('pagos.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar pago
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const patientSelect = document.getElementById('patient_id');
    const packageSelect = document.getElementById('patient_package_id');

    if (!patientSelect || !packageSelect) return;

    const urlTemplate = @json(route('api.pacientes.paquetes_v2', ['patient' => 0]));
    const oldPatientId = @json(old('patient_id'));
    const oldPackageId = @json(old('patient_package_id'));

    async function loadPackages(patientId, preselect = null) {
        packageSelect.innerHTML = '<option value="">Cargando...</option>';
        packageSelect.disabled = true;

        if (!patientId) {
            packageSelect.innerHTML = '<option value="">— Selecciona paciente primero —</option>';
            return;
        }

        const url = urlTemplate.replace('/0', '/' + patientId);

        try {
            const res = await fetch(url, {
                headers: { Accept: 'application/json' }
            });

            const items = await res.json();

            if (!Array.isArray(items) || items.length === 0) {
                packageSelect.innerHTML = '<option value="">— Sin paquetes activos disponibles —</option>';
                return;
            }

            packageSelect.innerHTML = '<option value="">— Selecciona —</option>';

            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.label;
                packageSelect.appendChild(option);
            });

            packageSelect.disabled = false;

            if (preselect) {
                packageSelect.value = String(preselect);
            }
        } catch (error) {
            packageSelect.innerHTML = '<option value="">Error al cargar paquetes</option>';
        }
    }

    patientSelect.addEventListener('change', () => {
        loadPackages(patientSelect.value);
    });

    if (oldPatientId) {
        patientSelect.value = oldPatientId;
        loadPackages(oldPatientId, oldPackageId);
    }
});
</script>
@endpush