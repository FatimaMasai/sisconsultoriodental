@php
    $links = [
        [  
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Dashboard',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('panel.*'),
            'can' => 'admin.dashboard',
        ],
        [  
            'icon' => 'fa-solid fa-users',
            'name' => 'Usuarios',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('users.*'),
            'can' => 'admin.users.index',
        ],
        [  
            'icon' => 'fa-solid fa-user-shield',
            'name' => 'Lista de Roles',
            'route' => route('admin.roles.index'),
            'active' => request()->routeIs('roles.*'),
            'can' => 'admin.roles.index',
        ],
        [  
            'icon' => 'fa-solid fa-cogs',
            'name' => 'Categoria de Servicio',
            'route' => route('admin.service_categories.index'),
            'active' => request()->routeIs('service_categories.*'),
            'can' => 'admin.service_categories.index',
        ],
        [
            'icon' => 'fa-solid fa-box',
            'name' => 'Servicio',
            'route' => route('admin.services.index'),
            'active' => request()->routeIs('services.*'),
            'can' => 'admin.services.index',
        ],
        [
            'icon' => 'fa-solid fa-users',
            'name' => 'Personas',
            'route' => route('admin.persons.index'),
            'active' => request()->routeIs('persons.*'),
            'can' => 'admin.persons.index',
            
        ],
        [
            'icon' => 'fa-solid fa-user-injured',
            'name' => 'Pacientes',
            'route' => route('admin.patients.index'),
            'active' => request()->routeIs('patients.*'),
            'can' => 'admin.patients.index',
            
        ],
        [
            'icon' => 'fa-solid fa-stethoscope',
            'name' => 'Especialidad',
            'route' => route('admin.specialities.index'),
            'active' => request()->routeIs('specialities.*'),
            'can' => 'admin.specialities.index',
            
        ],
        [
            'icon' => 'fa-solid fa-user-md',
            'name' => 'Doctor',
            'route' => route('admin.doctors.index'),
            'active' => request()->routeIs('doctors.*'),
            'can' => 'admin.doctors.index',
            
        ],
        [
            'icon' => 'fa-solid fa-dollar-sign',
            'name' => 'Ventas',
            'route' => route('admin.sales.index'),
            'active' => request()->routeIs('sales.*'),
            'can' => 'admin.sales.index',
            
        ],
        [
            'icon' => 'fa-solid fa-notes-medical',
            'name' => 'Historial Médico',
            'route' => route('admin.histories.index'),
            'active' => request()->routeIs('histories.*'),
            'can' => 'admin.histories.index',
            
        ],
        [
            'icon' => 'fa-solid fa-cogs',
            'name' => 'Categoria de Producto',
            'route' => route('admin.product_categories.index'),
            'active' => request()->routeIs('product_categories.*'),
            'can' => 'admin.product_categories.index',
            
        ],
        [
            'icon' => 'fa-solid fa-cube',
            'name' => 'Producto',
            'route' => route('admin.products.index'),
            'active' => request()->routeIs('products.*'),
            'can' => 'admin.products.index',
            
        ],
        [
            'icon' => 'fa-solid fa-truck',
            'name' => 'Proveedor',
            'route' => route('admin.suppliers.index'),
            'active' => request()->routeIs('suppliers.*'),
            'can' => 'admin.suppliers.index',
            
        ],
        [
            'icon' => 'fa-solid fa-shopping-cart',
            'name' => 'Compras',
            'route' => route('admin.purchases.index'),
            'active' => request()->routeIs('purchases.*'),
            'can' => 'admin.purchases.index',
            
        ]

        
    ];
@endphp

<aside id="logo-sidebar" 
    :class="{'translate-x-0 ease-out': sidebarOpen,
    '-translate-x-full ease-ind':!sidebarOpen}" 

        class="fixed top-0 left-0 
        
        {{-- z-[855] w-64 
        h-[100svh] --}}
    
        z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto 
    bg-white dark:bg-gray-800"> 
    
        <ul class="space-y-2 font-medium">
            @foreach ($links as $link) 
                @can($link['can'])
                    <li>
                        <a href="{{ $link['route'] }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                        {{ $link['active'] ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        
                            <span class="inline-flex w-6 h-6 justify-center items-center">
                                <i class="{{ $link['icon'] }} text-gray-500"> </i>
                            </span>

                            <span class="ml-2">
                                {{ $link['name'] }}
                            </span>
                        </a>
                    </li>
                @endcan
            @endforeach

        </ul>
    </div>
</aside>