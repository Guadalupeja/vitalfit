<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VitalFit') }} @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('img/brand/vitalfit-icon.png') }}">
</head>

<body class="min-h-screen text-gray-900" style="background-color: #F6F6F3;">
    @php
        $user = auth()->user();
        $userBranchesCount = $user?->branches()->count() ?? 0;
        $activeBranch = current_branch();
        $isBranchSelectionRoute = request()->routeIs('branches.select') || request()->routeIs('branches.select.store');
    @endphp

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('agenda.index') }}" class="inline-flex items-center">
                    <img
                        src="{{ asset('img/brand/vitalfit-logo-main.png') }}"
                        alt="VitalFit by Marisol Moreno"
                        class="h-12 w-auto sm:h-14"
                    >
                </a>
            </div>

            <div class="flex items-center gap-3">
                @if($activeBranch)
                    <div class="hidden sm:block text-right">
                        <p class="text-[11px] uppercase tracking-wide text-gray-500">Sucursal activa</p>
                        <p class="text-sm font-medium" style="color: #2F4F3E;">{{ $activeBranch->name }}</p>
                    </div>
                @endif

                @if($userBranchesCount > 1)
                <a href="{{ route('branches.select') }}" class="hidden sm:inline-flex vf-btn-secondary">
                    Cambiar sucursal
                </a>
                @endif

                <span class="hidden sm:inline text-sm text-gray-600">
                    {{ auth()->user()->name ?? '' }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                <button type="submit" class="vf-btn-primary">
                    Cerrar sesión
                </button>
                </form>
            </div>
        </div>

        {{-- Subheader --}}
        <div class="border-t border-gray-200" style="background-color: #F8F8F5;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-semibold">@yield('page_title', 'Panel')</h1>

                        <div class="mt-1 flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                            @hasSection('page_subtitle')
                                <p class="text-sm text-gray-600">@yield('page_subtitle')</p>
                            @endif

                            @if($activeBranch)
                            <span class="vf-badge">
                                Sucursal: {{ $activeBranch->name }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @yield('page_actions')
                    </div>
                </div>
            </div>
        </div>

        {{-- Menú --}}
        @unless($isBranchSelectionRoute)
            <nav class="bg-white border-t border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap gap-2 py-3">
                        @php
                        $linkClass = "inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition";
                    @endphp

                    <a href="{{ route('agenda.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('agenda.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Agenda
                    </a>

                    <a href="{{ route('pacientes.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('pacientes.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Pacientes
                    </a>

                    <a href="{{ route('pagos.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('pagos.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Pagos
                    </a>
@if(auth()->check() && auth()->user()->role === 'admin')
    <a href="{{ route('tabla_semanal.index') }}"
       class="{{ $linkClass }} {{ request()->routeIs('tabla_semanal.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
        Tabla semanal
    </a>
@endif

                    <a href="{{ route('tratamientos.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('tratamientos.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Tratamientos
                    </a>
    
                    <a href="{{ route('paquetes.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('paquetes.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Paquetes
                    </a>
                    <a href="{{ route('tipos-tratamiento.index') }}"
                    class="{{ $linkClass }} {{ request()->routeIs('tipos-tratamiento.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                        Tipos de tratamiento
                    </a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('inventario.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('inventario.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                            Inventario
                        </a>

                        <a href="{{ route('usuarios.index') }}"
                        class="{{ $linkClass }} {{ request()->routeIs('usuarios.*') ? 'vf-tab-active' : 'vf-tab-inactive' }}">
                            Usuarios
                        </a>
                    @endif

                    @if($userBranchesCount > 1)
                        <a href="{{ route('branches.select') }}"
                        class="{{ $linkClass }} vf-tab-inactive sm:hidden">
                            Cambiar sucursal
                        </a>
                    @endif
                    </div>

                    @if($activeBranch)
                        <div class="pb-3 sm:hidden">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500">Sucursal activa</p>
                            <p class="text-sm font-medium" style="color: #2F4F3E;">{{ $activeBranch->name }}</p>
                        </div>
                    @endif
                </div>
            </nav>
        @endunless
    </header>

    {{-- Contenido --}}
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg border p-4 text-sm"
                     style="background-color: #EEF7EE; color: #22543D; border-color: #B7D7BF;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg border p-4 text-sm"
                     style="background-color: #FEF2F2; color: #991B1B; border-color: #FECACA;">
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
    @stack('scripts')
</body>
</html>