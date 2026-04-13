@extends('layouts.app')

@section('title', ' | Seleccionar sucursal')
@section('page_title', 'Seleccionar sucursal')
@section('page_subtitle', 'Elige la sucursal con la que quieres trabajar.')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('branches.select.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Sucursal</label>
                    <select name="branch_id"
                            class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                        <option value="">— Selecciona una sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                        Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection