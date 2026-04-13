@php
    $isEdit = isset($paciente);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
        <input name="full_name" type="text"
               value="{{ old('full_name', $paciente->full_name ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        @error('full_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Teléfono (opcional)</label>
        <input name="phone" type="text"
               value="{{ old('phone', $paciente->phone ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- =========================================
         SOLO EN ALTA (CREAR): paquete inicial
         ========================================= --}}
    @if(!$isEdit)
        <div>
            <label class="block text-sm font-medium text-gray-700">Tratamiento inicial (opcional)</label>
            <select name="treatment_id"
                    class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option value="">— Sin tratamiento —</option>
                @foreach($treatments as $t)
                    <option value="{{ $t->id }}" @selected((string)old('treatment_id') === (string)$t->id)>
                        {{ $t->name }} ({{ $t->category }}) — {{ $t->duration_minutes }} min
                    </option>
                @endforeach
            </select>
            @error('treatment_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 mt-1">
                Si seleccionas un tratamiento y sesiones, el sistema creará automáticamente un paquete activo.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Sesiones compradas (paquete inicial)</label>
            <input name="sessions_purchased" type="number" min="0" max="999"
                   value="{{ old('sessions_purchased', 0) }}"
                   class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @error('sessions_purchased') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Total del paquete ($) (paquete inicial)</label>
            <input name="package_total" type="number" step="0.01" min="0"
                   value="{{ old('package_total', 0) }}"
                   class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @error('package_total') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    @endif

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
        <textarea name="notes" rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">{{ old('notes', $paciente->notes ?? '') }}</textarea>
        @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="active" value="1"
                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                   @checked(old('active', $paciente->active ?? true))>
            <span class="text-sm text-gray-700">Activo</span>
        </label>
        @error('active') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>