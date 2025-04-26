@php
    $links = [
        [  
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Dashboard fa',
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
        [  
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Categoria de Servicio',
            'route' => route('admin.service_categories.index'),
            'active' => request()->routeIs('service_categories.*'),
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Servicio',
            'route' => route('admin.services.index'),
            'active' => request()->routeIs('services.*'),
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Personas',
            'route' => route('admin.persons.index'),
            'active' => request()->routeIs('persons.*'),
            
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Pacientes',
            'route' => route('admin.patients.index'),
            'active' => request()->routeIs('patients.*'),
            
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Especialidad',
            'route' => route('admin.specialities.index'),
            'active' => request()->routeIs('specialities.*'),
            
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Doctor',
            'route' => route('admin.doctors.index'),
            'active' => request()->routeIs('doctors.*'),
            
        ],
        [
            'icon' => 'fa-solid fa-gauge',
            'name' => 'Ventas',
            'route' => route('admin.sales.index'),
            'active' => request()->routeIs('sales.*'),
            
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
            @endforeach

        </ul>
    </div>
</aside>