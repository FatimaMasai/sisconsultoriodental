<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-brands fa-google text-gray-400 mr-1"></i>
            Google Calendar
        </x-label>

        <a href="{{ route('admin.appointments.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la agenda
        </a>
    </div>

    @if (session('info'))
        <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            {{ session('info') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Conectá una cuenta de Google para que las citas que se agenden en el sistema se creen automáticamente
            como eventos en ese Google Calendar (uno solo, compartido para toda la clínica).
        </p>

        @if (! $packageInstalled)
            <div class="flex items-start gap-3 p-4 rounded-lg bg-yellow-50 dark:bg-gray-700 text-yellow-800 dark:text-yellow-300 text-sm">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <div>
                    Todavía falta instalar el paquete de Google en el proyecto. Corré esto en la terminal, en la
                    carpeta del proyecto:
                    <code class="block mt-2 bg-white dark:bg-gray-800 rounded px-2 py-1 text-xs">composer require google/apiclient</code>
                </div>
            </div>
        @elseif (! $configured)
            <div class="flex items-start gap-3 p-4 rounded-lg bg-yellow-50 dark:bg-gray-700 text-yellow-800 dark:text-yellow-300 text-sm">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <div>
                    El paquete ya está instalado, pero todavía faltan las credenciales de Google en tu archivo
                    <code>.env</code> (<code>GOOGLE_CALENDAR_CLIENT_ID</code>, <code>GOOGLE_CALENDAR_CLIENT_SECRET</code>,
                    <code>GOOGLE_CALENDAR_REDIRECT_URI</code>).
                </div>
            </div>
        @elseif ($connected)
            <div class="flex items-center gap-3 p-4 rounded-lg bg-green-50 dark:bg-gray-700 text-green-700 dark:text-green-400 text-sm mb-4">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    Conectado{{ $setting->google_account_email ? ' como ' . $setting->google_account_email : '' }}.
                    Las citas nuevas se sincronizan solas.
                </div>
            </div>

            <form action="{{ route('admin.settings.google.disconnect') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-red rounded-lg text-sm">
                    <i class="fa-solid fa-link-slash mr-1"></i> Desconectar
                </button>
            </form>
        @else
            <a href="{{ route('admin.settings.google.connect') }}" class="btn btn-blue rounded-lg text-sm">
                <i class="fa-brands fa-google mr-1"></i> Conectar Google Calendar
            </a>
        @endif
    </div>
</x-admin-layout>
