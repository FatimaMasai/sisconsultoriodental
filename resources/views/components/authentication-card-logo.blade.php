<a href="/">
    {{-- <svg class="size-16" viewbox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M11.395 44.428C4.557 40.198 0 32.632 0 24 0 10.745 10.745 0 24 0a23.891 23.891 0 0113.997 4.502c-.2 17.907-11.097 33.245-26.602 39.926z" fill="#6875F5"/>
        <path d="M14.134 45.885A23.914 23.914 0 0024 48c13.255 0 24-10.745 24-24 0-3.516-.756-6.856-2.115-9.866-4.659 15.143-16.608 27.092-31.75 31.751z" fill="#6875F5"/>
    </svg> --}}
   
    {{-- <img src="{{ asset('images/dental.png') }}" alt="Logo" class="w-32 h-32 mx-auto mb-4 rounded-full"> --}}
    {{-- El logo es un rectángulo negro sólido (así viene el archivo subido
         desde Configuración > Apariencia), por eso se ve "cuadrado": acá se
         le redondean las puntas y se le agrega el mismo marco turquesa que
         ya usan la chapita del menú y los PDF, para que quede prolijo y
         consistente con el resto del sistema en vez de un rectángulo pelado. --}}
    <img src="{{ \App\Models\ClinicSetting::instance()->logoUrl() }}" alt="{{ \App\Models\ClinicSetting::instance()->systemName() }}" class="auth-logo-img h-20 sm:h-24 w-auto mx-auto">

    <style>
        .auth-logo-img {
            border-radius: 14px;
            border: 2px solid #2dd4bf;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
    </style>
</a>
