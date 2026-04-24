@extends('layouts.app')

@section('title', ' | Editar tipo de tratamiento')
@section('page_title', 'Editar tipo de tratamiento')
@section('page_subtitle', 'Actualizar el nombre y color del tipo.')

@section('content')
    <div class="vf-card p-6">
        <form method="POST" action="{{ route('tipos-tratamiento.update', $tipoTratamiento) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('tipos-tratamiento._form', ['tipoTratamiento' => $tipoTratamiento])

            <div class="flex items-center gap-2">
                <a href="{{ route('tipos-tratamiento.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection