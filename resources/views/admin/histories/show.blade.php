<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-notes-medical text-gray-400 mr-1"></i>
            Detalle del Historial Médico
        </x-label>

        <a href="{{ route('admin.histories.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    @if($history)
        {{-- Información general --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $history->patient->person->name }} {{ $history->patient->person->last_name_father }} {{ $history->patient->person->last_name_mother }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Doctor</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $history->doctor->person->name }} {{ $history->doctor->person->last_name_father }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Servicio</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $history->service->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Fecha inicial</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($history->date)->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Descripción inicial</p>
                <p class="text-gray-900 dark:text-white">{{ $history->description }}</p>
            </div>
        </div>

        {{-- Fotos Antes y Después --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                <i class="fa-solid fa-camera text-gray-400 mr-1"></i>
                Fotos Antes y Después
            </x-label>

            @if ($errors->any())
                <div class="mb-4 p-3 text-sm text-red-700 bg-red-50 rounded-lg dark:bg-gray-700 dark:text-red-400">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                @foreach (['antes' => 'Antes', 'despues' => 'Después'] as $type => $label)
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-800 dark:text-white">{{ $label }}</h4>

                            @can('admin.histories.photos.store')
                                <label for="photo-input-{{ $type }}" class="btn btn-blue text-xs cursor-pointer">
                                    <i class="fa-solid fa-upload mr-1"></i> Subir foto
                                </label>
                                <form action="{{ route('admin.histories.photos.store', $history->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <input type="file" id="photo-input-{{ $type }}" name="photos[]"
                                        accept="image/*" multiple class="hidden"
                                        onchange="if (this.files.length) this.form.submit()">
                                </form>
                            @endcan
                        </div>

                        @php $photosOfType = $history->photos->where('type', $type); @endphp

                        @if ($photosOfType->count())
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                @foreach ($photosOfType as $photo)
                                    <div class="relative group">
                                        <button type="button" onclick="openPhotoLightbox('{{ $photo->url }}')" class="block w-full">
                                            <img src="{{ $photo->url }}" alt="Foto {{ $label }}"
                                                class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-90">
                                        </button>

                                        @can('admin.histories.photos.destroy')
                                            <form class="delete-form absolute top-1 right-1"
                                                action="{{ route('admin.histories.photos.destroy', $photo->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-red-600 text-white text-xs hover:bg-red-700">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no hay fotos de "{{ $label }}".</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Formulario para agregar nueva nota --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Agregar Consulta
            </x-label>

            <form action="{{ route('admin.histories.addNote', $history->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="note" id="note" rows="4"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                        placeholder="Escribí las observaciones de esta consulta..." required></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-green rounded-lg">
                        <i class="fa-solid fa-check mr-1"></i> Guardar Nota
                    </button>
                </div>
            </form>
        </div>

        {{-- Lista de notas --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Historial de Consultas
            </x-label>

            @if($history->notes->count() > 0)
                <div class="space-y-4">
                    @foreach($history->notes->sortByDesc('created_at') as $note)
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-l-4 border-green-500 shadow-md rounded-lg">
                            <p class="text-gray-800 dark:text-white">{{ $note->note }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <i class="fa-regular fa-clock mr-1"></i>
                                Agregado el {{ $note->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Info alert!</span> Todavia no hay consulta registrada.
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Visor de foto en grande: se abre sobre la misma página, sin
         mandar a otra pestaña ni perder la vista de la galería. --}}
    <div id="photo-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4" onclick="closePhotoLightbox()">
        <button type="button" onclick="closePhotoLightbox()" class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-white/90 text-gray-800 text-xl hover:bg-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="photo-lightbox-img" src="" alt="Foto ampliada" class="max-h-full max-w-full rounded-lg" onclick="event.stopPropagation()">
    </div>

    {{-- Confirmación antes de eliminar una foto (delegado en document para
         que funcione con cualquier foto, incluso subidas después de cargar
         la página) y el visor de foto en grande de arriba. --}}
    @push('js')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.delete-form');
            if (!form) return;

            e.preventDefault();

            Swal.fire({
                title: "¿Está seguro?",
                text: "¡No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, ¡eliminalo!",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        function openPhotoLightbox(url) {
            const lightbox = document.getElementById('photo-lightbox');
            document.getElementById('photo-lightbox-img').src = url;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }

        function closePhotoLightbox() {
            const lightbox = document.getElementById('photo-lightbox');
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.getElementById('photo-lightbox-img').src = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePhotoLightbox();
        });
    </script>
    @endpush

</x-admin-layout>
