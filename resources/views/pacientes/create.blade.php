@extends('layouts.app')

@section('title', ' | Nuevo paciente')
@section('page_title', 'Nuevo paciente')
@section('page_subtitle', 'Registrar paciente con tratamiento y sesiones.')

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('pacientes.store') }}" class="space-y-6">
            @csrf

            @include('pacientes._form', ['treatments' => $treatments])

            <div class="flex items-center gap-2">
                <a href="{{ route('pacientes.index') }}"
                   class="rounded-md border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                    Cancelar
                </a>

                <button type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
