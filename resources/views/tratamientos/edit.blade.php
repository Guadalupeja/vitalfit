@extends('layouts.app')

@section('title', ' | Editar tratamiento')
@section('page_title', 'Editar tratamiento')
@section('page_subtitle', 'Actualizar datos del tratamiento.')

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('tratamientos.update', $tratamiento) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('tratamientos._form')

            <div class="flex items-center gap-2">
                <a href="{{ route('tratamientos.index') }}"
                   class="rounded-md border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                    Volver
                </a>

                <button type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
