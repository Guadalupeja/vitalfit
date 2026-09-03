@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Especialista</label>
        <input
            type="text"
            value="{{ $nutritionUser->name }} (Nutrición)"
            disabled
            class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600"
        >
        <p class="mt-1 text-xs text-gray-500">
            Nutrición solo se asigna a este usuario.
        </p>
    </div>

    <div>
        <label for="branch_id" class="block text-sm font-semibold text-gray-700 mb-1">Sucursal</label>
        <select
            id="branch_id"
            name="branch_id"
            required
            class="w-full rounded-lg border-gray-300"
        >
            <option value="">— Selecciona —</option>
            @foreach($branches as $branch)
                <option
                    value="{{ $branch->id }}"
                    @selected(old('branch_id', $schedule->branch_id) == $branch->id)
                >
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="weekday" class="block text-sm font-semibold text-gray-700 mb-1">Día de la semana</label>
        <select
            id="weekday"
            name="weekday"
            required
            class="w-full rounded-lg border-gray-300"
        >
            <option value="">— Selecciona —</option>
            @foreach($weekdays as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('weekday', $schedule->weekday) == $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('weekday')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="service_type" class="block text-sm font-semibold text-gray-700 mb-1">Tipo de servicio</label>
        <select
            id="service_type"
            name="service_type"
            required
            class="w-full rounded-lg border-gray-300 bg-gray-100"
        >
            <option value="nutrition" selected>Nutrición</option>
        </select>
        @error('service_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-1">Hora inicio</label>
        <input
            id="start_time"
            name="start_time"
            type="time"
            value="{{ old('start_time', $schedule->start_time_short) }}"
            required
            class="w-full rounded-lg border-gray-300"
        >
        @error('start_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-1">Hora fin</label>
        <input
            id="end_time"
            name="end_time"
            type="time"
            value="{{ old('end_time', $schedule->end_time_short) }}"
            required
            class="w-full rounded-lg border-gray-300"
        >
        @error('end_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Notas</label>
        <textarea
            id="notes"
            name="notes"
            rows="3"
            class="w-full rounded-lg border-gray-300"
            placeholder="Ej. Horario temporal, solo esta temporada, etc."
        >{{ old('notes', $schedule->notes) }}</textarea>
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
                @checked(old('active', $schedule->active ?? true))
                class="rounded border-gray-300"
            >
            <span class="text-sm font-semibold text-gray-700">Horario activo</span>
        </label>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button
        type="submit"
        class="rounded-lg bg-[#6b7a57] px-4 py-2 text-white font-semibold hover:opacity-90"
    >
        Guardar horario
    </button>

    <a
        href="{{ route('horarios.index') }}"
        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 font-semibold hover:bg-gray-50"
    >
        Cancelar
    </a>
</div>