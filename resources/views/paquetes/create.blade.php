@extends('layouts.app')

@section('title', ' | Nuevo paquete')
@section('page_title', 'Nuevo paquete')
@section('page_subtitle', 'Crear un paquete reusable con varios tratamientos y sesiones.')

@section('content')
    <div class="vf-card p-6">
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
        <form method="POST" action="{{ route('paquetes.store') }}" class="space-y-6">
            @csrf

            @include('paquetes._form')

            <div class="flex items-center gap-2">
                <a href="{{ route('paquetes.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection