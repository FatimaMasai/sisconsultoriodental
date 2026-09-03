<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mi Consulta') }} — Sistema de gestión para consultorios médicos</title>
        <meta name="description" content="Ventas al contado y a crédito, historial de tus pacientes y control de pagos, todo en el mismo lugar — sin importar tu especialidad.">

        <!-- Ícono de la pestaña -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

        <!-- Google Analytics (GA4) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KP4LMY861N"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-KP4LMY861N');
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-14px); }
            }
            @keyframes blob {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(20px, -30px) scale(1.08); }
                66% { transform: translate(-15px, 15px) scale(0.95); }
            }
            @keyframes gradient-shift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            @keyframes fade-in-up {
                from { opacity: 0; transform: translateY(24px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes shimmer {
                0% { transform: translateX(-120%) skewX(-15deg); }
                100% { transform: translateX(220%) skewX(-15deg); }
            }
            .animate-float { animation: float 5s ease-in-out infinite; }
            .animate-blob { animation: blob 12s ease-in-out infinite; }
            .animate-blob-slow { animation: blob 18s ease-in-out infinite; }
            .animate-gradient { background-size: 200% 200%; animation: gradient-shift 8s ease infinite; }
            .hero-in { animation: fade-in-up .8s ease both; }
            .reveal {
                opacity: 0;
                transform: translateY(28px);
                transition: opacity .7s ease, transform .7s ease, background-color .3s ease, box-shadow .3s ease, border-color .3s ease;
            }
            .reveal.is-visible { opacity: 1; transform: translateY(0); }
            #site-header { transition: box-shadow .3s ease, background-color .3s ease; }
            #site-header.scrolled { box-shadow: 0 1px 12px rgba(15, 23, 42, .08); }

            /* Barra de progreso de scroll */
            #scroll-progress {
                position: fixed;
                top: 0; left: 0;
                height: 2px;
                width: 0%;
                background: linear-gradient(90deg, #2563eb, #2dd4bf, #2563eb);
                background-size: 200% 100%;
                animation: gradient-shift 6s ease infinite;
                z-index: 60;
            }

            /* Acento animado bajo los títulos de sección */
            .section-accent {
                display: block;
                height: 3px;
                width: 0;
                margin-top: .75rem;
                border-radius: 9999px;
                background: linear-gradient(90deg, #2563eb, #2dd4bf);
                background-size: 200% 200%;
                transition: width .8s ease .15s;
            }
            .section-accent-light { background: linear-gradient(90deg, #2dd4bf, #7dd3fc); background-size: 200% 200%; }
            .reveal.is-visible .section-accent { width: 3rem; animation: gradient-shift 4s ease infinite; }

            /* Brillo al pasar el mouse por los botones principales */
            .btn-shimmer { position: relative; overflow: hidden; }
            .btn-shimmer::after {
                content: '';
                position: absolute;
                top: 0; left: 0;
                width: 35%; height: 100%;
                background: linear-gradient(120deg, transparent, rgba(255,255,255,.4), transparent);
                transform: translateX(-120%) skewX(-15deg);
            }
            .btn-shimmer:hover::after { animation: shimmer .9s ease; }

            @media (prefers-reduced-motion: reduce) {
                .animate-float, .animate-blob, .animate-blob-slow, .animate-gradient, .hero-in { animation: none !important; }
                .reveal { opacity: 1; transform: none; transition: none; }
                .section-accent { width: 3rem; animation: none !important; }
                #scroll-progress { animation: none !important; }
                .btn-shimmer::after { display: none; }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-900 bg-white">

        <div id="scroll-progress"></div>

        @php
            $waNumber = '59175304552';
            $waLink = fn (string $mensaje) => 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($mensaje);
        @endphp

        {{-- ===== Header ===== --}}
        <header id="site-header" class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Mi Consulta" class="h-9 w-auto">
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#producto" class="relative pb-1 hover:text-slate-900 transition-colors after:content-[''] after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-teal-400 hover:after:w-full after:transition-all">Producto</a>
                    <a href="#precios" class="relative pb-1 hover:text-slate-900 transition-colors after:content-[''] after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-teal-400 hover:after:w-full after:transition-all">Precios</a>
                    <a href="#preguntas" class="relative pb-1 hover:text-slate-900 transition-colors after:content-[''] after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-teal-400 hover:after:w-full after:transition-all">Preguntas</a>
                </nav>

                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-400 hover:text-slate-700 transition-colors">
                    Ingresar
                </a>
            </div>
        </header>

        {{-- ===== Hero ===== --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-teal-50 -z-10"></div>
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-teal-200/40 rounded-full blur-3xl animate-blob -z-10"></div>
            <div class="absolute top-1/3 -right-20 w-80 h-80 bg-blue-200/40 rounded-full blur-3xl animate-blob-slow -z-10"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">
                <div class="hero-in">
                    <span class="inline-block text-[11px] font-bold tracking-widest text-teal-600 bg-teal-50 border border-teal-100 rounded-full px-3 py-1 mb-6">
                        PARA MÉDICOS INDEPENDIENTES
                    </span>

                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.1] text-slate-900">
                        Deja el cuaderno. Lleva tu consultorio en un
                        <span class="bg-gradient-to-r from-blue-600 via-teal-400 to-blue-600 bg-clip-text text-transparent animate-gradient">solo lugar</span>.
                    </h1>

                    <p class="mt-6 text-lg text-slate-600 max-w-md">
                        Ventas al contado y a crédito, historial de tus pacientes y control de pagos, todo en el mismo lugar — sin importar tu especialidad.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ $waLink('Hola, quiero agendar mi demo gratis de MiConsulta.') }}" target="_blank" rel="noopener"
                           class="btn-shimmer inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-blue-600 to-teal-400 text-white font-semibold px-6 py-3 shadow-lg shadow-blue-600/20 hover:opacity-90 hover:scale-105 hover:shadow-xl transition transform">
                            Solicita tu demo gratis
                        </a>
                        <a href="{{ $waLink('Hola, tengo una consulta sobre MiConsulta.') }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-slate-700 border-b-2 border-slate-900 pb-1 hover:text-slate-900 hover:border-teal-400 transition-colors">
                            Escríbeme por WhatsApp
                        </a>
                    </div>

                    <p class="mt-3 text-sm text-slate-500 italic">
                        Sin compromiso. Te muestro el sistema real por WhatsApp, en 15 minutos.
                    </p>
                </div>

                <div class="relative hero-in" style="animation-delay:.15s">
                    <div class="absolute -inset-4 bg-gradient-to-br from-blue-200/40 to-teal-200/40 rounded-3xl blur-2xl -z-10"></div>
                    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 p-6 max-w-sm ml-auto animate-float">
                        <div class="flex items-center justify-between text-xs mb-4">
                            <span class="font-semibold text-slate-400 tracking-wide">FICHA · #0231</span>
                            <span class="bg-teal-50 text-teal-600 font-semibold rounded-full px-2.5 py-1">Al día</span>
                        </div>

                        <p class="font-semibold text-slate-900 mb-4">Paciente: M. Rojas</p>

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-3">
                                <dt class="text-slate-500">Total del tratamiento</dt>
                                <dd class="font-medium text-slate-900">Bs 420.00</dd>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-3">
                                <dt class="text-slate-500">Cuota 1 — pagada</dt>
                                <dd class="font-medium text-teal-600">Bs 140.00</dd>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-3">
                                <dt class="text-slate-500">Cuota 2 — pagada</dt>
                                <dd class="font-medium text-teal-600">Bs 140.00</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Cuota 3 — pendiente</dt>
                                <dd class="font-medium text-slate-900">Bs 140.00</dd>
                            </div>
                        </dl>

                        <div class="flex justify-between items-center mt-5 pt-4 border-t border-slate-200">
                            <span class="font-bold text-slate-900">Saldo</span>
                            <span class="font-bold text-slate-900">Bs 140.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== ¿Te suena esto? ===== --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
            <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">¿Te suena esto?<span class="section-accent"></span></h2>

            <div class="mt-10 max-w-3xl space-y-5">
                @php
                    $dolores = [
                        'Anotas las ventas a crédito en un cuaderno y después no te acuerdas bien quién te debe ni cuánto.',
                        'Cuando un paciente te pregunta "¿cuánto me falta pagar?", tienes que ponerte a buscar en varias hojas.',
                        'El historial de cada paciente está repartido entre fichas de papel, WhatsApp y tu memoria.',
                        'A fin de mes te toca sentarte a sumar a mano cuánto vendiste y cuánto te deben todavía.',
                    ];
                @endphp

                @foreach ($dolores as $index => $dolor)
                    <div class="reveal flex items-start gap-3" style="transition-delay:{{ $index * 0.08 }}s">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-2.5 shrink-0"></span>
                        <p class="text-slate-700">{{ $dolor }}</p>
                    </div>
                @endforeach
            </div>

            <p class="reveal mt-8 max-w-3xl text-slate-600" style="transition-delay:.35s">
                Nada de esto es un drama. Pero cuesta plata: cada cuota que se te pasa por alto es un cobro que capaz nunca llega.
                Y cada hora armando cuentas a mano es una hora que no pasaste con un paciente.
            </p>
        </section>

        {{-- ===== MiConsulta hace eso por ti ===== --}}
        <section class="bg-slate-50 border-y border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">MiConsulta hace eso por ti<span class="section-accent"></span></h2>
                <p class="reveal mt-4 max-w-2xl text-lg text-slate-600" style="transition-delay:.05s">
                    Es un sistema web pensado para un consultorio como el tuyo — no para una clínica con veinte empleados.
                    Entras desde tu celular o tu computadora, registras la venta (al contado o a crédito) y el sistema se encarga
                    de avisarte qué está pendiente, qué está vencido y cuánto tienes por cobrar este mes.
                </p>
            </div>
        </section>

        {{-- ===== Así funciona en la práctica ===== --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">Así funciona en la práctica<span class="section-accent"></span></h2>

            <div class="mt-10 max-w-2xl divide-y divide-slate-200 border-t border-slate-200">
                @php
                    $pasos = [
                        'Registras al paciente una sola vez. Queda su ficha, con el historial de sus consultas.',
                        'Cada servicio que le vendes queda anotado — al contado o a crédito, con sus cuotas.',
                        'El panel de control te muestra de entrada, sin buscar nada: cuánto vendiste, cuánto compraste, cuánto te deben este mes y qué cuotas están vencidas.',
                        'Generas el comprobante en PDF al toque.',
                        'Si tienes recepcionista o algún colega atendiendo contigo, le creas su propio usuario. Tú decides qué puede ver y qué puede hacer.',
                    ];
                @endphp

                @foreach ($pasos as $index => $paso)
                    <div class="reveal group flex gap-6 py-5 px-3 -mx-3 rounded-lg transition-colors hover:bg-teal-50/60" style="transition-delay:{{ $index * 0.06 }}s">
                        <span class="text-sm font-bold text-teal-600 shrink-0 transition-transform group-hover:translate-x-1">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <p class="text-slate-700">{{ $paso }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== Qué cambia para ti ===== --}}
        <section class="bg-slate-50 border-y border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
                <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">Qué cambia para ti<span class="section-accent"></span></h2>

                <div class="mt-10 grid sm:grid-cols-2 gap-x-8 gap-y-5 max-w-4xl">
                    @php
                        $cambios = [
                            'Sabes exactamente quién te debe y cuánto, sin revisar cuaderno por cuaderno.',
                            'El historial de tus pacientes queda ordenado y a mano, aunque cambies de celular o de computadora.',
                            'Recuperas el tiempo que hoy gastas cuadrando cuentas a mano.',
                            'Si alguien más atiende en tu consultorio, todos ven la misma información, actualizada.',
                        ];
                    @endphp

                    @foreach ($cambios as $index => $cambio)
                        <div class="reveal flex items-start gap-3" style="transition-delay:{{ $index * 0.08 }}s">
                            <svg class="w-5 h-5 text-teal-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-slate-700">{{ $cambio }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===== Qué incluye ===== --}}
        <section id="producto" class="bg-slate-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
                <h2 class="reveal text-3xl font-extrabold tracking-tight text-white">Qué incluye<span class="section-accent section-accent-light"></span></h2>

                <div class="mt-10 space-y-5 max-w-2xl">
                    @php
                        $incluye = [
                            'El sistema instalado y funcionando con tu propio dominio — no compartes plataforma con otro consultorio.',
                            'Capacitación para que tú (o quien te ayude en recepción) lo uses sin vueltas desde el primer día.',
                            'Soporte por WhatsApp cuando tengas una duda. Me escribes a mí, no a un bot.',
                            'Respaldo de tus datos, para que no dependan de un cuaderno que se puede perder, mojar o quedarse sin hojas.',
                            'Acceso desde el celular y la computadora, cuando lo necesites.',
                        ];
                    @endphp

                    @foreach ($incluye as $index => $item)
                        <div class="reveal flex items-start gap-3" style="transition-delay:{{ $index * 0.06 }}s">
                            <span class="inline-block w-2 h-2 rounded-full bg-teal-400 mt-2 shrink-0"></span>
                            <p class="text-slate-300">{{ $item }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===== Por qué MiConsulta y no otra cosa ===== --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">Por qué MiConsulta y no otra cosa<span class="section-accent"></span></h2>

            <div class="mt-6 max-w-2xl space-y-4 text-slate-600">
                <p class="reveal">
                    Existen sistemas de gestión médica pensados para clínicas grandes, con módulos que un consultorio nunca
                    llega a usar — y los cobran como tal. MiConsulta cubre lo que un consultorio realmente necesita para el
                    día a día: ventas, créditos, compras, pacientes. Nada más, nada menos.
                </p>
                <p class="reveal" style="transition-delay:.08s">
                    Tampoco firmas ningún contrato. Si en algún momento decides que no es para ti, lo cancelas y ya. Sin letra chica.
                </p>
            </div>
        </section>

        {{-- ===== ¿Quién está detrás? ===== --}}
        <section class="bg-slate-50 border-y border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
                <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">¿Quién está detrás?<span class="section-accent"></span></h2>

                <div class="reveal mt-8 max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 sm:p-8" style="transition-delay:.05s">
                    <div class="flex items-center gap-4 mb-5">
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-teal-400 text-white font-extrabold text-lg shrink-0">FC</span>
                        <div>
                            <p class="font-bold text-slate-900">Fátima Chamo</p>
                            <p class="text-sm text-slate-500">Desarrolladora web</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-slate-600">
                        <p>
                            MiConsulta no nació como un producto genérico: lo construí primero para una dentista que solo
                            necesitaba lo básico para organizar su consultorio. De ahí le fui sumando lo que un consultorio
                            real termina necesitando — como el seguimiento de cuotas a crédito — a medida que aparecía la necesidad.
                        </p>
                        <p>
                            Si me escribes, hablas conmigo directamente. No hay equipo de soporte de por medio.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== La inversión ===== --}}
        <section id="precios" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">La inversión<span class="section-accent"></span></h2>

            <div class="mt-10 grid sm:grid-cols-2 gap-6 max-w-3xl">
                <div class="reveal bg-white rounded-xl border border-slate-200 p-6 transition-transform hover:-translate-y-1 hover:shadow-lg">
                    <span class="text-[11px] font-bold tracking-widest text-slate-400">MENSUAL</span>
                    <p class="mt-2 text-4xl font-extrabold text-slate-900">Bs 399<span class="text-base font-semibold text-slate-500">/mes</span></p>
                    <ul class="mt-6 space-y-2 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Soporte por WhatsApp incluido
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Respaldo de datos
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Actualizaciones del sistema
                        </li>
                    </ul>
                </div>

                <div class="reveal relative bg-white rounded-xl border-2 border-teal-400 p-6 transition-transform hover:-translate-y-1 hover:shadow-lg" style="transition-delay:.1s">
                    <span class="absolute -top-3 right-6 bg-teal-500 text-white text-[11px] font-bold tracking-widest px-3 py-1 rounded-full shadow">RECOMENDADO</span>
                    <span class="text-[11px] font-bold tracking-widest text-teal-600">ANUAL</span>
                    <p class="mt-2 text-4xl font-extrabold text-slate-900">Bs 1999<span class="text-base font-semibold text-slate-500">/año</span></p>
                    <p class="mt-1 text-sm text-slate-500">Equivale a Bs 166/mes</p>
                    <ul class="mt-6 space-y-2 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Soporte por WhatsApp incluido
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Respaldo de datos
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5"></span>
                            Actualizaciones del sistema
                        </li>
                    </ul>
                </div>
            </div>

            <a href="{{ $waLink('Hola, quiero mi demo gratis de MiConsulta.') }}" target="_blank" rel="noopener"
               class="btn-shimmer reveal mt-8 inline-flex items-center justify-center w-full max-w-3xl rounded-lg bg-sky-500 text-white font-semibold px-6 py-3.5 shadow-lg hover:bg-sky-400 hover:shadow-xl hover:scale-[1.02] transition transform"
               style="transition-delay:.2s">
                Quiero mi demo gratis
            </a>
            <p class="reveal mt-3 text-sm text-slate-500 italic" style="transition-delay:.25s">
                Te muestro el sistema funcionando de verdad, no una maqueta.
            </p>
        </section>

        {{-- ===== Sin vueltas, sin riesgo ===== --}}
        <section class="bg-slate-50 border-y border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
                <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">Sin vueltas, sin riesgo<span class="section-accent"></span></h2>

                <div class="mt-8 max-w-2xl space-y-4">
                    @php
                        $riesgos = [
                            'Antes de pagar nada, ves el sistema funcionando con tus propios ojos, en tu demo.',
                            'No hay contrato que te ate. Si decides cancelar, cancelas.',
                            'Tus datos quedan respaldados — no dependen de un solo cuaderno o de un celular que se puede perder.',
                        ];
                    @endphp

                    @foreach ($riesgos as $index => $riesgo)
                        <div class="reveal flex items-start gap-3" style="transition-delay:{{ $index * 0.08 }}s">
                            <svg class="w-5 h-5 text-teal-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-slate-700">{{ $riesgo }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===== Preguntas antes de decidir ===== --}}
        <section id="preguntas" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <h2 class="reveal text-3xl font-extrabold tracking-tight text-slate-900">Preguntas antes de decidir<span class="section-accent"></span></h2>

            <div class="mt-10 max-w-2xl divide-y divide-slate-200 border-t border-slate-200">
                @php
                    $faqs = [
                        ['¿Necesito saber de tecnología para usarlo?', 'No. Si sabes usar WhatsApp, puedes usar MiConsulta. Además, la capacitación inicial ya está incluida.'],
                        ['¿Qué pasa si dejo de pagar la mensualidad?', 'El sistema deja de estar disponible, pero no hay ninguna penalidad ni contrato que te obligue a seguir. Cancelas cuando quieras.'],
                        ['¿Necesito internet para usarlo?', 'Sí, es un sistema web: funciona desde el navegador de tu celular o tu computadora, sin instalar nada.'],
                        ['¿Sirve solo para una especialidad?', 'No. Sirve para cualquier consultorio que venda servicios al contado o a crédito y necesite un historial ordenado de sus pacientes.'],
                        ['¿Puedo darle acceso a mi recepcionista?', 'Sí. Puedes crear usuarios con roles distintos, para que cada quien vea solo lo que necesita ver.'],
                        ['¿Y si tengo una duda después de instalarlo?', 'Me escribes por WhatsApp. El soporte está incluido en la mensualidad.'],
                    ];
                @endphp

                @foreach ($faqs as $index => [$question, $answer])
                    <div class="reveal group py-5 flex gap-4 px-3 -mx-3 rounded-lg transition-colors hover:bg-slate-50" style="transition-delay:{{ $index * 0.06 }}s">
                        <span class="text-sm font-bold text-teal-600 shrink-0 transition-transform group-hover:scale-125">Q</span>
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $question }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== CTA final ===== --}}
        <section class="bg-blue-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20 text-center reveal">
                <h2 class="text-3xl font-extrabold tracking-tight text-white">Al final, se trata de esto</h2>
                <p class="mt-3 max-w-xl mx-auto text-blue-100">
                    No es sobre el sistema. Es sobre que dejes de perder plata por cuotas que se te olvidan, y que recuperes
                    el tiempo que hoy gastas cuadrando cuentas a mano. Eso es lo que hace MiConsulta por tu consultorio.
                </p>

                <a href="{{ $waLink('Hola, quiero solicitar mi demo gratis de MiConsulta.') }}" target="_blank" rel="noopener"
                   class="btn-shimmer mt-8 inline-flex items-center justify-center rounded-lg bg-sky-500 text-white font-semibold px-8 py-3.5 shadow-lg hover:bg-sky-400 hover:shadow-xl hover:scale-105 transition transform">
                    Solicita tu demo gratis
                </a>
                <p class="mt-3 text-sm text-blue-200 italic">
                    Sin compromiso. 15 minutos por WhatsApp para que la veas funcionar.
                </p>
            </div>
        </section>

        {{-- ===== Footer ===== --}}
        <footer class="bg-slate-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 text-center">
                <div class="inline-flex items-center gap-2 justify-center">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Mi Consulta" class="w-8 h-8">
                    <span class="font-extrabold text-white tracking-tight">MI CONSULTA</span>
                </div>

                <p class="mt-4 text-xs tracking-wide text-slate-400 uppercase">
                    MiConsulta — Sistema de gestión para consultorios médicos · Santa Cruz, Bolivia
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    © {{ now()->year }} Desarrollado por Fatima Chamo ·
                    <a href="{{ $waLink('Hola, tengo una consulta sobre MiConsulta.') }}" target="_blank" rel="noopener" class="text-teal-400 hover:text-teal-300 font-medium transition-colors border-b border-transparent hover:border-teal-300">WhatsApp</a>
                    ·
                    <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-300 font-medium transition-colors">Ingresar</a>
                </p>
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const header = document.getElementById('site-header');
                const progress = document.getElementById('scroll-progress');

                window.addEventListener('scroll', function () {
                    if (header) {
                        header.classList.toggle('scrolled', window.scrollY > 8);
                    }
                    if (progress) {
                        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                        const pct = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
                        progress.style.width = pct + '%';
                    }
                });

                const revealEls = document.querySelectorAll('.reveal');
                if ('IntersectionObserver' in window && revealEls.length) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

                    revealEls.forEach((el) => observer.observe(el));
                } else {
                    revealEls.forEach((el) => el.classList.add('is-visible'));
                }
            });
        </script>
    </body>
</html>
