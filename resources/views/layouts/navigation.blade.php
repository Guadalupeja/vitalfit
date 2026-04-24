@php
    $user = auth()->user();
    $userBranchesCount = $user?->branches()->count() ?? 0;
    $activeBranch = current_branch();
    $isBranchSelectionRoute = request()->routeIs('branches.select') || request()->routeIs('branches.select.store');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('agenda.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                @unless($isBranchSelectionRoute)
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">
                            {{ __('Agenda') }}
                        </x-nav-link>

                        <x-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')">
                            {{ __('Pacientes') }}
                        </x-nav-link>

                        <x-nav-link :href="route('pagos.index')" :active="request()->routeIs('pagos.*')">
                            {{ __('Pagos') }}
                        </x-nav-link>

                        <x-nav-link :href="route('tabla_semanal.index')" :active="request()->routeIs('tabla_semanal.*')">
                            {{ __('Tabla semanal') }}
                        </x-nav-link>

                        <x-nav-link :href="route('tratamientos.index')" :active="request()->routeIs('tratamientos.*')">
                            {{ __('Tratamientos') }}
                        </x-nav-link>
                    </div>
                @endunless
            </div>

            <!-- Settings / Branch Info -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if($activeBranch)
                    <div class="mr-4 text-right">
                        <p class="text-[11px] uppercase tracking-wide text-gray-500">Sucursal activa</p>
                        <p class="text-sm font-medium text-gray-900">{{ $activeBranch->name }}</p>
                    </div>
                @endif

                @if($userBranchesCount > 1)
                    <a href="{{ route('branches.select') }}"
                       class="mr-4 inline-flex items-center rounded-md border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cambiar sucursal
                    </a>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if($userBranchesCount > 1)
                            <x-dropdown-link :href="route('branches.select')">
                                {{ __('Cambiar sucursal') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                         this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        @unless($isBranchSelectionRoute)
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">
                    {{ __('Agenda') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')">
                    {{ __('Pacientes') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('pagos.index')" :active="request()->routeIs('pagos.*')">
                    {{ __('Pagos') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tabla_semanal.index')" :active="request()->routeIs('tabla_semanal.*')">
                    {{ __('Tabla semanal') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tratamientos.index')" :active="request()->routeIs('tratamientos.*')">
                    {{ __('Tratamientos') }}
                </x-responsive-nav-link>
            </div>
        @endunless

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            @if($activeBranch)
                <div class="px-4 py-2 mt-3 border-t border-gray-200">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500">Sucursal activa</div>
                    <div class="text-sm font-medium text-gray-900">{{ $activeBranch->name }}</div>
                </div>
            @endif

            <div class="mt-3 space-y-1">
                @if($userBranchesCount > 1)
                    <x-responsive-nav-link :href="route('branches.select')">
                        {{ __('Cambiar sucursal') }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                 this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>