@extends('layouts.app')

@section('title', ' | Editar pago')
@section('page_title', 'Editar pago')
@section('page_subtitle', 'Corregir monto, fecha, método o paquete del paciente.')

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

        <form method="POST"
              action="{{ route('pagos.update', $pago) }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

            @include('pagos._form', [
                'patients' => $patients,
                'pago' => $pago
            ])

            <div class="flex items-center gap-2 pt-2">
                <a href="{{ route('pagos.index') }}" class="vf-btn-secondary">
                    Volver
                </a>

                <button type="submit" class="vf-btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection