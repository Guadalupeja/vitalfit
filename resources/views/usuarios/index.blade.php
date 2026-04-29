@extends('layouts.app')

@section('title', ' | Usuarios')
@section('page_title', 'Usuarios')
@section('page_subtitle', 'Administración de usuarios, roles y sucursales.')

@section('page_actions')
    <a href="{{ route('usuarios.create') }}" class="vf-btn-primary">
        + Nuevo usuario
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="vf-card">
        <div class="flex items-center justify-between border-b border-gray-200 p-5">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $users->total() }}</span>
            </p>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Correo</th>
                        <th class="py-2 pr-4">Rol</th>
                        <th class="py-2 pr-4">Sucursales</th>
                        <th class="py-2 pr-4">Estatus</th>
                        <th class="py-2 pr-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($users as $user)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="py-3 pr-4 text-gray-700">{{ $user->email }}</td>
                        <td class="py-3 pr-4 text-gray-700">
                            {{ $user->role === 'admin' ? 'Admin' : 'Especialista' }}
                        </td>
                        <td class="py-3 pr-4 text-gray-700">
                            @if($user->branches->isEmpty())
                                <span class="text-gray-400">Sin sucursales</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->branches as $branch)
                                        <span class="rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs text-gray-700">
                                            {{ $branch->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            @if($user->active)
                                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <a href="{{ route('usuarios.edit', $user) }}" class="font-medium text-[var(--vf-primary)] hover:underline">
                                Editar
                            </a>

                            <form action="{{ route('usuarios.destroy', $user) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-3 font-medium text-red-600 hover:underline" type="submit">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection