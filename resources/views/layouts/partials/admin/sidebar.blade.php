@php
    $menuModules = [

    'Dashboard' => [
            [
                'icon' => 'fa-solid fa-gauge',
                'name' => 'Panel de Control',
                'route' => route('admin.dashboard'),
                'active' => request()->routeIs('panel.*'),
                'can' => 'admin.dashboard',
            ],


        ],
        'Agenda' => [
            [
                'icon' => 'fa-solid fa-calendar-days',
                'name' => 'Citas',
                'route' => route('admin.appointments.index'),
                'active' => request()->routeIs('admin.appointments.*'),
                'can' => 'admin.appointments.index',
            ],
        ],
        'Administracion' => [
            [
                'icon' => 'fa-solid fa-users',
                'name' => 'Usuarios',
                'route' => route('admin.users.index'),
                'active' => request()->routeIs('admin.users.*'),
                'can' => 'admin.users.index',
            ],
            [
                'icon' => 'fa-solid fa-user-shield',
                'name' => 'Lista de Roles',
                'route' => route('admin.roles.index'),
                'active' => request()->routeIs('admin.roles.*'),
                'can' => 'admin.roles.index',
            ],
            [
                'icon' => 'fa-solid fa-clipboard-list',
                'name' => 'Auditoría',
                'route' => route('admin.audit_logs.index'),
                'active' => request()->routeIs('admin.audit_logs.*'),
                'can' => 'admin.audit_logs.index',
            ],
        ],
        'Servicio y especilidad' => [ 
            [
                'icon' => 'fa-solid fa-box',
                'name' => 'Servicio',
                'route' => route('admin.services.index'),
                'active' => request()->routeIs('admin.services.*'),
                'can' => 'admin.services.index',
            ],
            [
                'icon' => 'fa-solid fa-stethoscope',
                'name' => 'Especialidad',
                'route' => route('admin.specialities.index'),
                'active' => request()->routeIs('admin.specialities.*'),
                'can' => 'admin.specialities.index',
            ],
        ],
        'Categorias de' => [ 
            [
                'icon' => 'fa-solid fa-cogs',
                'name' => 'Servicio',
                'route' => route('admin.service_categories.index'),
                'active' => request()->routeIs('admin.service_categories.*'),
                'can' => 'admin.service_categories.index',
            ],
            [
                'icon' => 'fa-solid fa-cogs',
                'name' => 'Producto',
                'route' => route('admin.product_categories.index'),
                'active' => request()->routeIs('admin.product_categories.*'),
                'can' => 'admin.product_categories.index',
            ],
        ],

        'Recepción' => [
            [
                'icon' => 'fa-solid fa-users',
                'name' => 'Datos personales',
                'route' => route('admin.persons.index'),
                'active' => request()->routeIs('admin.persons.*'),
                'can' => 'admin.persons.index',
            ],
            [
                'icon' => 'fa-solid fa-user-injured',
                'name' => 'Pacientes',
                'route' => route('admin.patients.index'),
                'active' => request()->routeIs('admin.patients.*'),
                'can' => 'admin.patients.index',
            ],
            
            [
                'icon' => 'fa-solid fa-user-md',
                'name' => 'Doctor',
                'route' => route('admin.doctors.index'),
                'active' => request()->routeIs('admin.doctors.*'),
                'can' => 'admin.doctors.index',
            ],
            [
                'icon' => 'fa-solid fa-truck',
                'name' => 'Proveedor',
                'route' => route('admin.suppliers.index'),
                'active' => request()->routeIs('admin.suppliers.*'),
                'can' => 'admin.suppliers.index',
            ],
        ],
        'Comprobante' => [
            [
                'icon' => 'fa-solid fa-dollar-sign',
                'name' => 'Comprobante de Servicio',
                'route' => route('admin.sales.index'),
                'active' => request()->routeIs('admin.sales.*'),
                'can' => 'admin.sales.index',
            ],
            [
                'icon' => 'fa-solid fa-notes-medical',
                'name' => 'Historial Médico',
                'route' => route('admin.histories.index'),
                'active' => request()->routeIs('admin.histories.*'),
                'can' => 'admin.histories.index',
            ],
            [
                'icon' => 'fa-solid fa-money-check-dollar',
                'name' => 'Cuotas Pagadas',
                'route' => route('admin.installments.paid'),
                'active' => request()->routeIs('admin.installments.*'),
                'can' => 'admin.sales.index',
            ],
        ],
        'Compras de insumos' => [
            
            [
                'icon' => 'fa-solid fa-cube',
                'name' => 'Producto',
                'route' => route('admin.products.index'),
                'active' => request()->routeIs('admin.products.*'),
                'can' => 'admin.products.index',
            ],
            [
                'icon' => 'fa-solid fa-shopping-cart',
                'name' => 'Compras',
                'route' => route('admin.purchases.index'),
                'active' => request()->routeIs('admin.purchases.*'),
                'can' => 'admin.purchases.index',
            ], 
        ],
        
    ];
@endphp


<aside id="logo-sidebar" :class="{'translate-x-0 ease-out': sidebarOpen, '-translate-x-full ease-ind': !sidebarOpen}" class="fixed top-0 left-0 z-40 w-64 h-screen h-[100dvh] pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-20 overflow-y-auto overscroll-contain touch-pan-y bg-white dark:bg-gray-800" style="-webkit-overflow-scrolling: touch;">
        <ul class="space-y-2 font-medium">

            @foreach ($menuModules as $module => $links)
                <li class="text-xs uppercase text-gray-500 dark:text-gray-400 pl-2 pt-4">
                    {{ $module }}
                </li>

                @foreach ($links as $link)
                    @can($link['can'])
                        <li>
                            <a href="{{ $link['route'] }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100
                             dark:hover:bg-gray-700 group {{ $link['active'] ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                <span class="inline-flex w-6 h-6 justify-center items-center">
                                        {{-- <i class="{{ $link['icon'] }} text-gray-500"></i> --}}
                                    <i class="{{ $link['icon'] }} {{ $link['active'] ? 'text-teal-600' : 'text-gray-500' }}"></i>
                                </span>
                                <span class="ml-2">{{ $link['name'] }}</span>
                            </a>
                        </li>
                    @endcan
                @endforeach
            @endforeach

        </ul>
    </div>
</aside>
