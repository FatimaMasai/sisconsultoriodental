<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Dashboard
    </x-label>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total de Ventas -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Ventas Totales
            </x-label>

            @if ($totalSales > 0)
                <p class="text-2xl font-bold text-green-500">{{ $totalSales }} Bs.</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Bs. 0</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Compras -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Compras Totales
            </x-label> 

            @if ($totalPurchases > 0)
                <p class="text-2xl font-bold text-yellow-500">{{ $totalPurchases }} Bs.</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Bs. 0</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Productos -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Productos 
            </x-label>
            @if ($totalProducts > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalProducts }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay productos</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Pacientes -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Pacientes 
            </x-label> 
            @if ($totalPatients > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalPatients }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay pacientes</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total de Proveedores -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Proveedores 
            </x-label> 
 
            @if ($totalSuppliers > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalSuppliers }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay proveedores</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Doctores -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Doctores 
            </x-label>

            @if ($totalDoctors > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalDoctors }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay doctores</span>
                    </div>
                </div>
            @endif
        </div>
        <!-- Total de Doctores -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Especialidades 
            </x-label>
            @if ($totalSpecialities > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalSpecialities }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay especialidades</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Servicios -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Servicios 
            </x-label>
            @if ($totalServices > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalServices }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay servicios</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

</x-admin-layout>