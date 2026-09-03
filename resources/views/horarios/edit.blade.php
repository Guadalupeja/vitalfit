@extends('layouts.app')

@section('title', ' | Editar horario')
@section('page_title', 'Editar horario de nutrición')
@section('page_subtitle', 'Actualiza día, sucursal u horario de atención.')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="vf-card">
            <form method="POST" action="{{ route('horarios.update', $schedule) }}" class="p-6">
                @csrf
                @method('PUT')

                @include('horarios._form')
            </form>
        </div>
    </div>
@endsection