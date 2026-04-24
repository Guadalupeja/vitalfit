<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4" style="background-color: #F6F6F3;">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <img
                    src="{{ asset('img/brand/vitalfit-logo-main.png') }}"
                    alt="VitalFit by Marisol Moreno"
                    class="mx-auto h-20 w-auto"
                >
                <p class="mt-3 text-sm text-gray-600">
                    Acceso al sistema interno
                </p>
            </div>

            <div class="vf-card p-6 sm:p-8">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo</label>
                        <input id="email" class="vf-input mt-1" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input id="password" class="vf-input mt-1" type="password" name="password" required autocomplete="current-password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[var(--vf-primary)] focus:ring-[var(--vf-primary)]" name="remember">
                            <span>Recordarme</span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="vf-btn-primary w-full">
                            Iniciar sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>