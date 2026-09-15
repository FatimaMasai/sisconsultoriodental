<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
            <button x-on:click="sidebarOpen = !sidebarOpen"
            
            data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>
            @php $logoUrl = \App\Models\ClinicSetting::instance()->logoUrl(); @endphp
            {{-- El logo tiene fondo transparente, el texto "wellness" en
                 turquesa y el subtítulo "CENTRO INTEGRAL" en blanco. Por
                 eso va en una "chapita" negra fija (no cambia con el tema):
                 así el subtítulo blanco se lee bien, con un borde turquesa
                 (el mismo color de marca) para que la insignia se distinga
                 del menú de alrededor. --}}
            <a href="{{ route('admin.dashboard')}}"
                class="brand-badge flex items-center ms-2 md:me-6 px-3 py-1.5">
                <img src="{{ $logoUrl }}" alt="Wellness Centro Integral" class="brand-logo-full" style="height: 40px; width: auto;">
                <img src="{{ $logoUrl }}" alt="Wellness Centro Integral" class="brand-logo-icon" style="height: 28px; width: auto; display: none;">
            </a>
            </div>

            {{-- En celular el logo completo (con el texto "Control para tu
                 Consultorio") ocupa mucho ancho al lado del botón de menú,
                 el modo oscuro y el usuario. Acá abajo de 640px (el mismo
                 quiebre "sm" que ya usa el resto del layout para el menú
                 lateral) se muestra una versión más chica y con menos
                 relleno, para que todo entre cómodo en una sola fila. --}}
            <style>
                {{-- Estilos del "chip" del logo escritos acá como CSS
                     normal (no clases de Tailwind) para que se vean
                     siempre, sin depender de si el bundle de Tailwind ya
                     se recompiló con esta combinación en particular. --}}
                .brand-badge {
                    background-color: #000;
                    border: 2px solid #2dd4bf;
                    border-radius: 12px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
                }

                @media (max-width: 639px) {
                    .brand-logo-full { display: none !important; }
                    .brand-logo-icon { display: inline-block !important; }
                    .brand-badge { padding: 0.25rem 0.5rem !important; }
                }
            </style>
            <div class="flex items-center">
                {{-- Botón de modo claro/oscuro: cada usuario elige el suyo,
                     se guarda en este navegador (localStorage) y se lee de
                     nuevo antes de pintar la página en layouts/admin.blade.php
                     para que no haya parpadeo. --}}
                <button type="button"
                    x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    x-on:click="
                        dark = !dark;
                        document.documentElement.classList.toggle('dark', dark);
                        localStorage.setItem('theme', dark ? 'dark' : 'light');
                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark } }));
                    "
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 mr-2"
                    title="Cambiar tema claro/oscuro">
                    <i class="fa-solid fa-moon" x-show="!dark"></i>
                    <i class="fa-solid fa-sun" x-show="dark" style="display: none;"></i>
                    <span class="sr-only">Cambiar tema</span>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </button>
                        @else
                            <span class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}

                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('API Tokens') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-200"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf

                            <x-dropdown-link href="{{ route('logout') }}"
                                     @click.prevent="$root.submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>