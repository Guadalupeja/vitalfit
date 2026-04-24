@php
    $isEdit = isset($tratamiento);

    $selectedTypeId = old('treatment_type_id', $tratamiento->treatment_type_id ?? '');

    $selectedType = collect($treatmentTypes ?? [])->firstWhere('id', $selectedTypeId);

    $selectedTypeColor = $selectedType->color_hex
        ?? ($tratamiento->type->color_hex ?? '#E5E7EB');

    $selectedTypeName = $selectedType->name
        ?? ($tratamiento->type->name ?? 'Sin tipo seleccionado');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input
            name="name"
            type="text"
            value="{{ old('name', $tratamiento->name ?? '') }}"
            class="vf-input mt-1"
            required
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tipo de tratamiento</label>
        <select
            id="treatment_type_id"
            name="treatment_type_id"
            class="vf-input mt-1"
            required
        >
            <option value="">— Selecciona —</option>
            @foreach($treatmentTypes as $type)
                <option
                    value="{{ $type->id }}"
                    data-color="{{ $type->color_hex }}"
                    data-name="{{ $type->name }}"
                    @selected((string) old('treatment_type_id', $tratamiento->treatment_type_id ?? '') === (string) $type->id)
                >
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        @error('treatment_type_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Duración (min)</label>
        <input
            name="duration_minutes"
            type="number"
            min="10"
            max="360"
            value="{{ old('duration_minutes', $tratamiento->duration_minutes ?? 60) }}"
            class="vf-input mt-1"
            required
        >
        @error('duration_minutes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Color del tipo seleccionado</p>
                    <p id="treatment-type-name" class="mt-1 text-sm text-gray-500">
                        {{ $selectedTypeName }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        id="treatment-type-color-preview"
                        class="h-10 w-10 rounded-full border border-gray-300"
                        style="background: {{ $selectedTypeColor }}"
                    ></span>

                    <span
                        id="treatment-type-color-hex"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700"
                    >
                        {{ $selectedTypeColor }}
                    </span>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                El color se define en el catálogo de tipos de tratamiento y se usará automáticamente en la agenda.
            </p>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
        <textarea
            name="description"
            rows="3"
            class="vf-input mt-1"
        >{{ old('description', $tratamiento->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="active"
                value="1"
                class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                @checked(old('active', $tratamiento->active ?? true))
            >
            <span class="text-sm text-gray-700">Activo</span>
        </label>
        @error('active')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('treatment_type_id');
    const preview = document.getElementById('treatment-type-color-preview');
    const hexLabel = document.getElementById('treatment-type-color-hex');
    const nameLabel = document.getElementById('treatment-type-name');

    if (!typeSelect || !preview || !hexLabel || !nameLabel) return;

    function updateTypePreview() {
        const option = typeSelect.options[typeSelect.selectedIndex];

        if (!option || !option.value) {
            preview.style.background = '#E5E7EB';
            hexLabel.textContent = '#E5E7EB';
            nameLabel.textContent = 'Sin tipo seleccionado';
            return;
        }

        const color = option.dataset.color || '#E5E7EB';
        const name = option.dataset.name || 'Tipo';

        preview.style.background = color;
        hexLabel.textContent = color;
        nameLabel.textContent = name;
    }

    typeSelect.addEventListener('change', updateTypePreview);
    updateTypePreview();
});
</script>