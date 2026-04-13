<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VitalFit') }} @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Puedes poner el logo aquí si luego lo agregas en /public --}}
                {{-- <img src="{{ asset('img/logo.png') }}" alt="VitalFit" class="h-9 w-auto"> --}}
                <div>
                    <p class="text-lg font-semibold leading-tight">VitalFit</p>
                    <p class="text-xs text-gray-500 -mt-1">Sistema interno</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-sm text-gray-600">
                    {{ auth()->user()->name ?? '' }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        {{-- Subheader (título de página + acciones) --}}
        <div class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-semibold">@yield('page_title', 'Panel')</h1>
                        <p class="text-sm text-gray-600">@yield('page_subtitle')</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @yield('page_actions')
                    </div>
                </div>
            </div>
        </div>

        {{-- Menú --}}
        <nav class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-2 py-3">

                    @php
                        $linkClass = "inline-flex items-center rounded-md px-3 py-2 text-sm font-medium";
                        $inactive = "text-gray-700 hover:bg-gray-100";
                        $active = "bg-gray-900 text-white";
                    @endphp

                    <a href="{{ route('agenda.index') }}"
                       class="{{ $linkClass }} {{ request()->routeIs('agenda.*') ? $active : $inactive }}">
                        Agenda
                    </a>

                    <a href="{{ route('pacientes.index') }}"
                       class="{{ $linkClass }} {{ request()->routeIs('pacientes.*') ? $active : $inactive }}">
                        Pacientes
                    </a>

                    <a href="{{ route('pagos.index') }}"
                       class="{{ $linkClass }} {{ request()->routeIs('pagos.*') ? $active : $inactive }}">
                        Pagos
                    </a>

                    <a href="{{ route('tabla_semanal.index') }}"
                       class="{{ $linkClass }} {{ request()->routeIs('tabla_semanal.*') ? $active : $inactive }}">
                        Tabla semanal
                    </a>

                    <a href="{{ route('tratamientos.index') }}"
                       class="{{ $linkClass }} {{ request()->routeIs('tratamientos.*') ? $active : $inactive }}">
                        Tratamientos
                    </a>
                </div>
            </div>
        </nav>
    </header>

    {{-- Contenido --}}
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Alertas/flash --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500">
                © {{ date('Y') }} VitalFit — Sistema interno
            </p>
            <p class="text-sm text-gray-500">
                Desarrollado por Guadalupe Juárez Arias
            </p>
        </div>
    </footer>
</body>
</html>
