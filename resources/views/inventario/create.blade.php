@extends('layouts.app')

@section('title', ' | Nuevo producto de inventario')
@section('page_title', 'Nuevo producto de inventario')
@section('page_subtitle', 'Registrar producto disponible en la sucursal actual.')

@section('content')
    <div class="vf-card p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-medium">Hay errores en el formulario:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventario.store') }}" class="space-y-6">
            @csrf

            @include('inventario._form', ['item' => $item])

            <div class="flex items-center gap-2">
                <a href="{{ route('inventario.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
@endsection