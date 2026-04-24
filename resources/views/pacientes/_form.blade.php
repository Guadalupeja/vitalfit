@php
    $isEdit = isset($paciente);

    $packageTemplatesJson = collect($packageTemplates ?? [])->map(function ($template) {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'total_price' => (float) $template->total_price,
            'items' => collect($template->items ?? [])->map(function ($item) {
                return [
                    'treatment_name' => $item->treatment->name ?? 'Tratamiento',
                    'sessions_included' => $item->sessions_included,
                    'color_hex' => $item->treatment->color_hex ?? '#9CA3AF',
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
        <input
            name="full_name"
            type="text"
            value="{{ old('full_name', $paciente->full_name ?? '') }}"
            class="vf-input mt-1"
            required
        >
        @error('full_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Teléfono (opcional)</label>
        <input
            name="phone"
            type="text"
            value="{{ old('phone', $paciente->phone ?? '') }}"
            class="vf-input mt-1"
        >
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @unless($isEdit)
        <div>
            <label class="block text-sm font-medium text-gray-700">Paquete inicial (opcional)</label>
            <select
                id="package_template_id"
                name="package_template_id"
                class="vf-input mt-1"
            >
                <option value="">— Sin paquete inicial —</option>
                @foreach($packageTemplates as $template)
                    <option
                        value="{{ $template->id }}"
                        @selected((string) old('package_template_id') === (string) $template->id)
                    >
                        {{ $template->name }} — ${{ number_format((float) $template->total_price, 2) }}
                    </option>
                @endforeach
            </select>

            <p class="mt-1 text-xs text-gray-500">
                Al seleccionar un paquete, se copiarán automáticamente sus tratamientos y sesiones al guardar el paciente.
            </p>

            @error('package_template_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="package-preview-wrapper" class="hidden md:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Paquete seleccionado</p>
                        <p id="package-preview-name" class="mt-1 text-lg font-semibold text-gray-900">—</p>
                    </div>

                    <div class="text-left md:text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total del paquete</p>
                        <p id="package-preview-total" class="mt-1 text-lg font-semibold text-gray-900">$0.00</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="mb-2 text-sm font-medium text-gray-700">Tratamientos incluidos</p>
                    <div id="package-preview-items" class="space-y-2"></div>
                </div>
            </div>
        </div>
    @endunless

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
        <textarea
            name="notes"
            rows="3"
            class="vf-input mt-1"
        >{{ old('notes', $paciente->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="active"
                value="1"
                class="rounded border-gray-300 text-[var(--vf-primary)] focus:ring-[var(--vf-primary)]"
                @checked(old('active', $paciente->active ?? true))
            >
            <span class="text-sm text-gray-700">Activo</span>
        </label>
        @error('active')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@unless($isEdit)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('package_template_id');
            const previewWrapper = document.getElementById('package-preview-wrapper');
            const previewName = document.getElementById('package-preview-name');
            const previewTotal = document.getElementById('package-preview-total');
            const previewItems = document.getElementById('package-preview-items');

            if (!select || !previewWrapper || !previewName || !previewTotal || !previewItems) {
                return;
            }

            const templates = @json($packageTemplatesJson);

            function renderPreview() {
                const selectedId = select.value;

                if (!selectedId) {
                    previewWrapper.classList.add('hidden');
                    previewName.textContent = '—';
                    previewTotal.textContent = '$0.00';
                    previewItems.innerHTML = '';
                    return;
                }

                const template = templates.find(item => String(item.id) === String(selectedId));

                if (!template) {
                    previewWrapper.classList.add('hidden');
                    previewName.textContent = '—';
                    previewTotal.textContent = '$0.00';
                    previewItems.innerHTML = '';
                    return;
                }

                previewWrapper.classList.remove('hidden');
                previewName.textContent = template.name;
                previewTotal.textContent = '$' + Number(template.total_price).toFixed(2);

                previewItems.innerHTML = '';

                template.items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'rounded-lg border border-gray-200 bg-white p-3';

                    row.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded" style="background:${item.color_hex}"></span>
                            <span class="font-medium text-gray-800">${item.treatment_name}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">${item.sessions_included} sesiones incluidas</p>
                    `;

                    previewItems.appendChild(row);
                });
            }

            select.addEventListener('change', renderPreview);
            renderPreview();
        });
    </script>
@endunless