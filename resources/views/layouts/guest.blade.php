<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php $clinicSetting = \App\Models\ClinicSetting::instance(); @endphp

        <title>{{ $clinicSetting->systemName() }}</title>

        <!-- Ícono de la pestaña -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

        {{-- Mismo color de marca configurable (Configuración > Apariencia)
             que ya usa el layout de admin, para que el botón/links de las
             pantallas de acceso (login, registro, etc.) sigan la marca del
             cliente en vez de quedar con el índigo genérico de Jetstream. --}}
        <style>
            :root {
                --brand-primary: {{ $clinicSetting->brandColor() }};
            }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Kit de iconos Font Awesome: el layout de admin ya lo tenía, pero
             acá (login, registro, etc.) faltaba, así que el botón de
             mostrar/ocultar contraseña quedaba sin el ícono del ojito
             visible aunque el botón sí funcionaba. --}}
        <script src="https://kit.fontawesome.com/e4c0eaccaf.js" crossorigin="anonymous"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
