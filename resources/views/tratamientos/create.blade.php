@extends('layouts.app')

@section('title', ' | Nuevo tratamiento')
@section('page_title', 'Nuevo tratamiento')
@section('page_subtitle', 'Crear un tratamiento usando un tipo previamente definido.')

@section('content')
    <div class="vf-card p-6">
        <form method="POST" action="{{ route('tratamientos.store') }}" class="space-y-6">
            @csrf

            @include('tratamientos._form', ['treatmentTypes' => $treatmentTypes])

            <div class="flex items-center gap-2">
                <a href="{{ route('tratamientos.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection