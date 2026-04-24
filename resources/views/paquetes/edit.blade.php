@extends('layouts.app')

@section('title', ' | Editar paquete')
@section('page_title', 'Editar paquete')
@section('page_subtitle', 'Actualizar la composición del paquete.')

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
        <form method="POST" action="{{ route('paquetes.update', $paquete) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('paquetes._form')

            <div class="flex items-center gap-2">
                <a href="{{ route('paquetes.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection