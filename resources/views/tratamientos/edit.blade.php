@extends('layouts.app')

@section('title', ' | Editar tratamiento')
@section('page_title', 'Editar tratamiento')
@section('page_subtitle', 'Actualizar los datos del tratamiento.')

@section('content')
    <div class="vf-card p-6">
        <form method="POST" action="{{ route('tratamientos.update', $tratamiento) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('tratamientos._form', [
                'tratamiento' => $tratamiento,
                'treatmentTypes' => $treatmentTypes
            ])

            <div class="flex items-center gap-2">
                <a href="{{ route('tratamientos.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection