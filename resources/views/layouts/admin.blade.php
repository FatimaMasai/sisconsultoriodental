<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


       {{-- agregando el script de la libreria de sweetalert2 PASO 1--}}
       <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



        {{-- kit de iconos font awesome --}}
        <script src="https://kit.fontawesome.com/e4c0eaccaf.js" crossorigin="anonymous"></script>


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased" 

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
            <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
                
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
        
        @livewireScripts 



    </body>
</html>
