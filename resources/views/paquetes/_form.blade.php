@php
    $isEdit = isset($paquete);
    $oldItems = old('items');

    if (!$oldItems && $isEdit) {
        $oldItems = $paquete->items->map(function ($item) {
            return [
                'treatment_id' => $item->treatment_id,
                'sessions_included' => $item->sessions_included,
            ];
        })->toArray();
    }

    if (!$oldItems) {
        $oldItems = [
            ['treatment_id' => '', 'sessions_included' => 1],
        ];
    }
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre del paquete</label>
        <input name="name" type="text" required
               value="{{ old('name', $paquete->name ?? '') }}"
               class="vf-input mt-1">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Descripción</label>
        <textarea name="description" rows="3" class="vf-input mt-1">{{ old('description', $paquete->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Total del paquete</label>
        <input name="total_price" type="number" step="0.01" min="0.01" required
               value="{{ old('total_price', $paquete->total_price ?? 0) }}"
               class="vf-input mt-1">
        @error('total_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="active" value="1"
                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                   @checked(old('active', $paquete->active ?? true))>
            <span class="text-sm text-gray-700">Activo</span>
        </label>
    </div>
</div>

<div class="mt-8">
    <div class="mb-3 flex items-center justify-between">
        <div>
            <p class="font-semibold text-gray-900">Tratamientos incluidos</p>
            <p class="text-sm text-gray-500">Agrega los tratamientos que forman este paquete y sus sesiones.</p>
        </div>

        <button type="button" id="btnAddPackageItem" class="vf-btn-secondary">
            + Agregar tratamiento
        </button>
    </div>

    <div id="packageItemsWrapper" class="space-y-3">
        @foreach($oldItems as $index => $item)
            <div class="package-item-row rounded-xl border border-gray-200 bg-white p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tratamiento</label>
                        <select name="items[{{ $index }}][treatment_id]" required class="vf-input mt-1">
                            <option value="">— Selecciona —</option>
                            @foreach($treatments as $treatment)
                                <option value="{{ $treatment->id }}"
                                    @selected((string)$item['treatment_id'] === (string)$treatment->id)>
                                    {{ $treatment->name }} ({{ $treatment->category }})
                                </option>
                            @endforeach
                        </select>
                        @error("items.$index.treatment_id") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sesiones incluidas</label>
                        <input type="number" min="1" required name="items[{{ $index }}][sessions_included]"
                               value="{{ $item['sessions_included'] }}"
                               class="vf-input mt-1">
                        @error("items.$index.sessions_included") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btnRemovePackageItem text-sm font-medium text-red-600 hover:underline">
                        Eliminar este tratamiento
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="packageItemTemplate">
    <div class="package-item-row rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tratamiento</label>
                <select class="vf-input mt-1 item-treatment" required>
                    <option value="">— Selecciona —</option>
                    @foreach($treatments as $treatment)
                        <option value="{{ $treatment->id }}">
                            {{ $treatment->name }} ({{ $treatment->category }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Sesiones incluidas</label>
                <input type="number" min="1" value="1" required class="vf-input mt-1 item-sessions">
            </div>
        </div>

        <div class="mt-3">
            <button type="button" class="btnRemovePackageItem text-sm font-medium text-red-600 hover:underline">
                Eliminar este tratamiento
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('packageItemsWrapper');
    const addBtn = document.getElementById('btnAddPackageItem');
    const template = document.getElementById('packageItemTemplate');

    if (!wrapper || !addBtn || !template) return;

    function syncIndexes() {
        const rows = wrapper.querySelectorAll('.package-item-row');

        rows.forEach((row, index) => {
            const treatmentSelect = row.querySelector('.item-treatment, select[name*="[treatment_id]"]');
            const sessionsInput = row.querySelector('.item-sessions, input[name*="[sessions_included]"]');

            if (treatmentSelect) {
                treatmentSelect.name = `items[${index}][treatment_id]`;
            }

            if (sessionsInput) {
                sessionsInput.name = `items[${index}][sessions_included]`;
            }
        });
    }

    addBtn.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        wrapper.appendChild(clone);
        syncIndexes();
    });

    wrapper.addEventListener('click', (e) => {
        if (e.target.classList.contains('btnRemovePackageItem')) {
            const rows = wrapper.querySelectorAll('.package-item-row');

            if (rows.length <= 1) {
                alert('El paquete debe tener al menos un tratamiento.');
                return;
            }

            e.target.closest('.package-item-row')?.remove();
            syncIndexes();
        }
    });

    syncIndexes();
});
</script>
@endpush