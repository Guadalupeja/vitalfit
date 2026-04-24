@extends('layouts.app')

@section('title', ' | Nuevo paciente')
@section('page_title', 'Nuevo paciente')
@section('page_subtitle', 'Registrar paciente con tratamiento y sesiones.')

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

        <form method="POST" action="{{ route('pacientes.store') }}" class="space-y-6">
            @csrf

        @include('pacientes._form', ['packageTemplates' => $packageTemplates])
            <div class="flex items-center gap-2">
                <a href="{{ route('pacientes.index') }}" class="vf-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection