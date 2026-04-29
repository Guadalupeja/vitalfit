@extends('layouts.app')

@section('title', ' | Editar usuario')
@section('page_title', 'Editar usuario')
@section('page_subtitle', 'Actualizar rol, estado y sucursales del usuario.')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-700">Corrige lo siguiente:</p>
            <ul class="mt-2 list-disc pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="vf-card p-6">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('usuarios._form', [
                'usuario' => $usuario,
                'branches' => $branches
            ])

            <div class="flex items-center gap-2">
                <a href="{{ route('usuarios.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection