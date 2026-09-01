@php
    $isEdit = isset($pago);
    $isAdmin = auth()->user()?->role === 'admin';
    $todayStart = now()->format('Y-m-d') . 'T00:00';
    $todayEnd = now()->format('Y-m-d') . 'T23:59';
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Paciente</label>
        <select name="patient_id" id="patient_id" class="vf-input mt-1" required>
            <option value="">— Selecciona —</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" @selected(old('patient_id', $pago->patient_id ?? null) == $patient->id)>
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
                value="{{ old('paid_at', isset($pago) ? $pago->paid_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                class="vf-input mt-1"
                required
                @unless($isAdmin)
                    min="{{ $todayStart }}"
                    max="{{ $todayEnd }}"
                @endunless
            >
            @if(!$isAdmin)
                <p class="mt-1 text-xs text-gray-500">
                    Como especialista, solo puedes registrar o corregir pagos del día actual.
                </p>
            @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Monto</label>
        <input type="number"
               step="0.01"
               min="0.01"
               name="amount"
               value="{{ old('amount', $pago->amount ?? '') }}"
               class="vf-input mt-1"
               required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Método</label>
        <select name="method" class="vf-input mt-1" required>
            <option value="">— Selecciona —</option>
            <option value="cash" @selected(old('method', $pago->method ?? '') === 'cash')>Efectivo</option>
            <option value="transfer" @selected(old('method', $pago->method ?? '') === 'transfer')>Transferencia</option>
            <option value="card" @selected(old('method', $pago->method ?? '') === 'card')>Tarjeta</option>
            <option value="other" @selected(old('method', $pago->method ?? '') === 'other')>Otro</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Referencia (opcional)</label>
        <input type="text"
               name="reference"
               value="{{ old('reference', $pago->reference ?? '') }}"
               class="vf-input mt-1">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
        <textarea name="notes" rows="3" class="vf-input mt-1">{{ old('notes', $pago->notes ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Comprobante (opcional)</label>
        <input type="file" name="receipt" class="vf-input mt-1">

        @if($isEdit && !empty($pago->receipt_path))
            <p class="mt-2 text-sm text-gray-600">
                Archivo actual:
                <a href="{{ asset('storage/' . $pago->receipt_path) }}" target="_blank" class="font-medium text-[var(--vf-primary)] hover:underline">
                    Ver comprobante
                </a>
            </p>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const patientSelect = document.getElementById('patient_id');
    const packageSelect = document.getElementById('patient_package_id');

    if (!patientSelect || !packageSelect) return;

    const urlTemplate = @json(route('api.pacientes.paquetes_v2', ['patient' => 0]));
    const oldPatientId = @json(old('patient_id', $pago->patient_id ?? null));
    const oldPackageId = @json(old('patient_package_id', $pago->patient_package_id ?? null));

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