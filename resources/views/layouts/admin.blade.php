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

        {{-- Evita el "flash" de tema equivocado: lee la preferencia de
             modo oscuro guardada en este navegador (Configuración lo
             guarda con el botón del menú superior) ANTES de que se pinte
             la página, así nunca se ve un parpadeo claro→oscuro. --}}
        <script>
            (function () {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        {{-- Color de marca y tamaño de letra configurables desde
             Configuración > Apariencia (sin esto, cambiarlos implicaría
             tocar código y volver a compilar). Los botones/menú que los
             usan leen estas variables, con los valores de siempre como
             respaldo si todavía no se configuró nada. --}}
        <style>
            html {
                font-size: {{ $clinicSetting->fontSizePercent() }};
                /* Le avisa al navegador que la página ya controla su
                   propio modo claro/oscuro (con la clase .dark), para que
                   Chrome/Edge no intenten "oscurecer" por su cuenta los
                   elementos a los que no les pusimos un color explícito
                   (eso es lo que causaba fondos y contrastes raros). */
                color-scheme: light;
            }

            html.dark {
                color-scheme: dark;
            }

            :root {
                --brand-primary: {{ $clinicSetting->brandColor() }};
            }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


       {{-- agregando el script de la libreria de sweetalert2 PASO 1--}}
       <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

       {{-- libreria para generar el comprobante de pago como imagen (compartir por WhatsApp) --}}
       <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>



        {{-- kit de iconos font awesome --}}
        <script src="https://kit.fontawesome.com/e4c0eaccaf.js" crossorigin="anonymous"></script>


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100"

        x-data="{sidebarOpen: false}"
        :class="{
            'overflow-hidden': sidebarOpen 
        }">  

        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 sm:hidden
        transition-opacity" x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        style="display: none;"
        x-show="sidebarOpen"
        x-on:click="sidebarOpen = false">

        </div>

        @include('layouts.partials.admin.navigation')
        @include('layouts.partials.admin.sidebar')
        
        <div class="p-4 sm:ml-64">
            <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-20">
                
                {{$slot}}

            </div>
        </div>

        {{-- agregando el script de la libreria de sweetalert2 PASO 2--}}
        @stack('js')
        @if (session('swal'))
            <script>
                Swal.fire({!! json_encode(session('swal'))!!});
            </script>

        @endif

        @include('layouts.partials.admin.appointment-alerts')

        @livewireScripts



    </body>
</html>
