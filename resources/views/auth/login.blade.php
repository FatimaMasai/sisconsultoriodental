<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

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
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4" x-data="{ showPassword: false }">
                <x-label for="password" value="{{ __('Password') }}" />
                <div class="relative">
                    <x-input id="password" class="block mt-1 w-full pr-10" type="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" />
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

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ms-4">
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
            <p class="mt-4 text-center text-sm text-gray-600">
                {{ __('¿Todavía no tenés una cuenta?') }}
                <a class="underline font-medium text-gray-900 hover:text-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                    {{ __('Registrate acá') }}
                </a>
            </p>
        @endif
    </x-authentication-card>
</x-guest-layout>
