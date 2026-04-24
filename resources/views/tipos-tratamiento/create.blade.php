@extends('layouts.app')

@section('title', ' | Nuevo tipo de tratamiento')
@section('page_title', 'Nuevo tipo de tratamiento')
@section('page_subtitle', 'Crear un tipo y definir el color que usará en la agenda.')

@section('content')
    <div class="vf-card p-6">
        <form method="POST" action="{{ route('tipos-tratamiento.store') }}" class="space-y-6">
            @csrf

            @include('tipos-tratamiento._form')

            <div class="flex items-center gap-2">
                <a href="{{ route('tipos-tratamiento.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection