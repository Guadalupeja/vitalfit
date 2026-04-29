@php
    $isEdit = isset($usuario);
    $selectedBranches = collect(old('branch_ids', $isEdit ? $usuario->branches->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input
            type="text"
            name="name"
            value="{{ old('name', $usuario->name ?? '') }}"
            class="vf-input mt-1"
            required
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Correo</label>
        <input
            type="email"
            name="email"
            value="{{ old('email', $usuario->email ?? '') }}"
            class="vf-input mt-1"
            required
        >
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Rol</label>
        <select name="role" class="vf-input mt-1" required>
            <option value="">— Selecciona —</option>
            <option value="admin" @selected(old('role', $usuario->role ?? '') === 'admin')>Admin</option>
            <option value="specialist" @selected(old('role', $usuario->role ?? '') === 'specialist')>Especialista</option>
        </select>
        @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">
            {{ $isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña' }}
        </label>
        <input
            type="password"
            name="password"
            class="vf-input mt-1"
            {{ $isEdit ? '' : 'required' }}
        >
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">
            {{ $isEdit ? 'Confirmar nueva contraseña' : 'Confirmar contraseña' }}
        </label>
        <input
            type="password"
            name="password_confirmation"
            class="vf-input mt-1"
            {{ $isEdit ? '' : 'required' }}
        >
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Sucursales asignadas</label>

        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach($branches as $branch)
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2">
                    <input
                        type="checkbox"
                        name="branch_ids[]"
                        value="{{ $branch->id }}"
                        class="rounded border-gray-300 text-[var(--vf-primary)] focus:ring-[var(--vf-primary)]"
                        @checked(in_array((string) $branch->id, $selectedBranches, true))
                    >
                    <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                </label>
            @endforeach
        </div>

        @error('branch_ids')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('branch_ids.*')
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
                @checked(old('active', $usuario->active ?? true))
            >
            <span class="text-sm text-gray-700">Activo</span>
        </label>
        @error('active')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>