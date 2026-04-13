@php
    $isEdit = isset($tratamiento);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input name="name" type="text"
               value="{{ old('name', $tratamiento->name ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tipo</label>
        <select name="category"
                class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @foreach($categories as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $tratamiento->category ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Duración (min)</label>
        <input name="duration_minutes" type="number" min="10" max="360"
               value="{{ old('duration_minutes', $tratamiento->duration_minutes ?? 60) }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        @error('duration_minutes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Color (HEX)</label>
        <input name="color_hex" type="text" placeholder="#AABBCC"
               value="{{ old('color_hex', $tratamiento->color_hex ?? '#EC4899') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        <p class="text-xs text-gray-500 mt-1">Ejemplo: #EC4899</p>
        @error('color_hex') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-end gap-2">
        <span class="text-sm text-gray-600">Vista previa:</span>
        <span class="h-6 w-10 rounded border border-gray-200"
              style="background: {{ old('color_hex', $tratamiento->color_hex ?? '#EC4899') }}"></span>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
        <textarea name="description" rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">{{ old('description', $tratamiento->description ?? '') }}</textarea>
        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="active" value="1"
                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                   @checked(old('active', $tratamiento->active ?? true))>
            <span class="text-sm text-gray-700">Activo</span>
        </label>
        @error('active') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>
