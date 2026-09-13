<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            Historial Clínico
        </x-label>

        <div class="flex flex-wrap items-center gap-3">
            @can('admin.histories.index')
                <a href="{{ route('admin.histories.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                    Ver historial anterior (archivo)
                </a>
            @endcan

            @can('admin.histories.create')
                <a href="{{ route('admin.expedientes.create') }}" class="btn btn-green rounded-lg text-sm">
                    <i class="fa-solid fa-folder-plus mr-1"></i> Nuevo Expediente
                </a>
            @endcan
        </div>
    </div>

    <livewire:admin.expediente-search />
</x-admin-layout>
