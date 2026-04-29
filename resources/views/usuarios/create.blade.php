@extends('layouts.app')

@section('title', ' | Nuevo usuario')
@section('page_title', 'Nuevo usuario')
@section('page_subtitle', 'Crear un usuario y asignarle rol y sucursales.')

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
        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
            @csrf

            @include('usuarios._form', ['branches' => $branches])

            <div class="flex items-center gap-2">
                <a href="{{ route('usuarios.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection