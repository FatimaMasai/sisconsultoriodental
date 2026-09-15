<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-gray-900">{{ __('Iniciar sesión') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Ingresá tus datos para acceder al panel') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        @session('info')
            <div class="mb-4 flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50" role="alert">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" class="auth-label" />
                <x-input id="email" class="auth-input block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4" x-data="{ showPassword: false }">
                <x-label for="password" value="{{ __('Password') }}" class="auth-label" />
                <div class="relative">
                    <x-input id="password" class="auth-input block mt-1.5 w-full pr-10" type="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" />
                    <button type="button" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                        x-on:click="showPassword = !showPassword">
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span class="sr-only">{{ __('Show password') }}</span>
                    </button>
                </div>
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-6">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-500 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="auth-button">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        {{-- Registro público: cualquiera que entre a esta pantalla puede
             crearse una cuenta acá. Queda sin ningún rol hasta que el
             admin se lo asigna desde Usuarios > Agregar Rol (Doctor,
             Recepcionista, etc.); hasta entonces no puede entrar al
             panel. --}}
        @if (Route::has('register'))
            <p class="mt-6 pt-5 border-t border-gray-100 text-center text-sm text-gray-600">
                {{ __('¿Todavía no tenés una cuenta?') }}
                <a class="auth-link font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2" href="{{ route('register') }}">
                    {{ __('Registrate acá') }}
                </a>
            </p>
        @endif
    </x-authentication-card>

    {{-- Estos estilos van acá (en vez de solo clases de Tailwind) para que
         el look de marca (color configurable en Configuración > Apariencia)
         se vea siempre, sin depender de que esa combinación exacta ya esté
         en el CSS compilado. --}}
    <style>
        .auth-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .auth-input {
            border-radius: 0.6rem;
            border-color: #e5e7eb;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .auth-input:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 20%, transparent);
            outline: none;
        }

        .auth-link {
            color: var(--brand-primary);
        }

        .auth-link:hover {
            color: color-mix(in srgb, var(--brand-primary) 80%, black);
        }

        .auth-button {
            background-color: var(--brand-primary);
            border-radius: 0.6rem;
            padding: 0.65rem 1.5rem;
            font-size: 0.8125rem;
        }

        .auth-button:hover,
        .auth-button:focus {
            background-color: color-mix(in srgb, var(--brand-primary) 85%, black);
        }
    </style>
</x-guest-layout>
