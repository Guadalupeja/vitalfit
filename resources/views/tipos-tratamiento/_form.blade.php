@php
    $isEdit = isset($tipoTratamiento);

    $presetColors = [
        '#EC4899', '#DB2777', '#C026D3', '#A855F7', '#8B5CF6',
        '#7C3AED', '#6366F1', '#4F46E5', '#3B82F6', '#2563EB',
        '#0EA5E9', '#0284C7', '#06B6D4', '#0891B2', '#14B8A6',
        '#0F766E', '#10B981', '#059669', '#22C55E', '#16A34A',
        '#84CC16', '#65A30D', '#EAB308', '#CA8A04', '#F59E0B',
        '#D97706', '#F97316', '#EA580C', '#EF4444', '#DC2626',
        '#B91C1C', '#6B7280', '#4B5563', '#374151', '#111827',
    ];

    $selectedColor = old('color_hex', $tipoTratamiento->color_hex ?? '#EC4899');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre del tipo</label>
        <input
            name="name"
            type="text"
            value="{{ old('name', $tipoTratamiento->name ?? '') }}"
            class="vf-input mt-1"
            required
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Color del tipo</label>

        <div class="mt-3">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Colores sugeridos</p>

            <div class="flex flex-wrap gap-3" id="color-palette">
                @foreach($presetColors as $color)
                    <button
                        type="button"
                        class="color-swatch h-10 w-10 rounded-full border-2 transition hover:scale-105"
                        data-color="{{ $color }}"
                        style="background: {{ $color }}; border-color: {{ strtoupper($selectedColor) === strtoupper($color) ? '#111827' : '#E5E7EB' }};"
                        title="{{ $color }}"
                    ></button>
                @endforeach
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Selector de color</label>
                <input
                    id="color_picker"
                    type="color"
                    value="{{ $selectedColor }}"
                    class="mt-1 h-12 w-full cursor-pointer rounded-lg border border-gray-300 bg-white p-1"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Código HEX</label>
                <input
                    id="color_hex"
                    name="color_hex"
                    type="text"
                    value="{{ $selectedColor }}"
                    class="vf-input mt-1"
                    placeholder="#EC4899"
                    required
                >
                @error('color_hex')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Vista previa</label>
                <div
                    id="color-preview"
                    class="mt-1 h-12 w-full rounded-lg border border-gray-200"
                    style="background: {{ $selectedColor }}"
                ></div>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="active"
                value="1"
                class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                @checked(old('active', $tipoTratamiento->active ?? true))
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
    const colorInput = document.getElementById('color_hex');
    const colorPicker = document.getElementById('color_picker');
    const preview = document.getElementById('color-preview');
    const swatches = document.querySelectorAll('.color-swatch');

    if (!colorInput || !colorPicker || !preview) return;

    function normalizeHex(value) {
        if (!value) return '';
        let hex = value.trim().toUpperCase();
        if (!hex.startsWith('#')) hex = '#' + hex;
        return hex;
    }

    function isValidHex(value) {
        return /^#([A-F0-9]{6})$/i.test(value);
    }

    function updateSwatches(activeHex) {
        swatches.forEach((swatch) => {
            const swatchColor = (swatch.dataset.color || '').toUpperCase();
            swatch.style.borderColor = swatchColor === activeHex ? '#111827' : '#E5E7EB';
        });
    }

    function applyColor(rawValue) {
        const hex = normalizeHex(rawValue);
        if (!isValidHex(hex)) return;

        colorInput.value = hex;
        colorPicker.value = hex;
        preview.style.background = hex;
        updateSwatches(hex);
    }

    swatches.forEach((swatch) => {
        swatch.addEventListener('click', () => applyColor(swatch.dataset.color || ''));
    });

    colorPicker.addEventListener('input', () => applyColor(colorPicker.value));

    colorInput.addEventListener('input', () => {
        const hex = normalizeHex(colorInput.value);
        if (isValidHex(hex)) applyColor(hex);
    });

    colorInput.addEventListener('blur', () => {
        const hex = normalizeHex(colorInput.value);
        if (isValidHex(hex)) applyColor(hex);
    });

    applyColor(colorInput.value || '{{ $selectedColor }}');
});
</script>