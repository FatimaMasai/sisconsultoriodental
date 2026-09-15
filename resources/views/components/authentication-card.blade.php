<div class="auth-shell min-h-screen flex flex-col justify-center items-center px-4 py-10">
    <div class="mb-6">
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 px-6 py-8 sm:px-10">
        {{ $slot }}
    </div>

    <p class="mt-8 text-xs text-gray-400 text-center">
        &copy; {{ now()->year }} {{ \App\Models\ClinicSetting::instance()->systemName() }}
    </p>

    {{-- Fondo con un degradé suave (en vez del gris plano de antes) para que
         la pantalla no se vea tan vacía arriba/abajo de la tarjeta. Va acá
         adentro (no como clase de Tailwind) para que se vea siempre igual
         sin depender de que esa combinación ya esté en el CSS compilado. --}}
    <style>
        .auth-shell {
            background: linear-gradient(180deg, #f8fafa 0%, #eef3f2 55%, #e6f2f0 100%);
        }
    </style>
</div>
