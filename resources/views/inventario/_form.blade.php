<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Producto</label>
        <input type="text"
               name="product"
               value="{{ old('product', $item->product) }}"
               class="vf-input mt-1"
               required>
        @error('product')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Presentación</label>
        <input type="text"
               name="presentation"
               value="{{ old('presentation', $item->presentation) }}"
               class="vf-input mt-1"
               placeholder="Ej. 500 ml, caja, frasco, paquete">
        @error('presentation')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Segmento</label>
        <input type="text"
               name="segment"
               value="{{ old('segment', $item->segment) }}"
               class="vf-input mt-1"
               placeholder="Ej. Faciales, Aparatología, Nutrición">
        @error('segment')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Fecha de entrada</label>
        <input type="date"
               name="entry_date"
               value="{{ old('entry_date', optional($item->entry_date)->format('Y-m-d')) }}"
               class="vf-input mt-1">
        @error('entry_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Fecha de caducidad</label>
        <input type="date"
               name="expiration_date"
               value="{{ old('expiration_date', optional($item->expiration_date)->format('Y-m-d')) }}"
               class="vf-input mt-1">
        @error('expiration_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Cantidad</label>
        <input type="number"
               name="quantity"
               step="0.01"
               min="0"
               value="{{ old('quantity', $item->quantity ?? 0) }}"
               class="vf-input mt-1"
               required>
        @error('quantity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Unidad</label>
        <input type="text"
               name="unit"
               value="{{ old('unit', $item->unit ?? 'piezas') }}"
               class="vf-input mt-1"
               required
               placeholder="piezas, ml, cajas, frascos">
        @error('unit')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Stock mínimo</label>
        <input type="number"
               name="minimum_stock"
               step="0.01"
               min="0"
               value="{{ old('minimum_stock', $item->minimum_stock) }}"
               class="vf-input mt-1"
               placeholder="Opcional">
        <p class="mt-1 text-xs text-gray-500">
            Si la cantidad llega a este número o menos, aparecerá como bajo stock.
        </p>
        @error('minimum_stock')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center pt-7">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox"
                   name="active"
                   value="1"
                   class="rounded border-gray-300 text-[var(--vf-primary)] focus:ring-[var(--vf-primary)]"
                   @checked(old('active', $item->active ?? true))>
            Producto activo
        </label>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Notas</label>
        <textarea name="notes"
                  rows="4"
                  class="vf-input mt-1"
                  placeholder="Observaciones internas">{{ old('notes', $item->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>