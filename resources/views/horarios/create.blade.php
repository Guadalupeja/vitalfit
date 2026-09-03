@extends('layouts.app')

@section('title', ' | Nuevo horario')
@section('page_title', 'Nuevo horario de nutrición')
@section('page_subtitle', 'Define cuándo Marisol atiende nutrición en una sucursal.')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="vf-card">
            <form method="POST" action="{{ route('horarios.store') }}" class="p-6">
                @include('horarios._form')
            </form>
        </div>
    </div>
@endsection