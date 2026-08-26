{{-- Aviso al personal de que una cita está por empezar (15 minutos antes).
     Corre en segundo plano en cualquier pantalla del sistema mientras el
     usuario tenga sesión iniciada y permiso para ver la agenda: consulta
     periódicamente admin.appointments.upcoming_alerts y, si hay una cita
     dentro de los próximos 15 minutos que todavía no se avisó en este
     navegador, muestra una notificación (del navegador si el usuario dio
     permiso, o una ventana emergente si no). --}}
@can('admin.appointments.index')
<script>
(function () {
    const ENDPOINT = @json(route('admin.appointments.upcoming_alerts'));
    const LOGO = @json(asset('images/logo-icon.png'));
    const POLL_MS = 30000; // revisar cada 30 segundos
    const STORAGE_KEY = 'mc_citas_notificadas';

    function leerNotificadas() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function marcarNotificada(id) {
        const data = leerNotificadas();
        data[id] = Date.now();

        // limpiar avisos de más de 12 horas para no acumular basura en el navegador
        const limite = Date.now() - (12 * 60 * 60 * 1000);
        Object.keys(data).forEach(function (key) {
            if (data[key] < limite) delete data[key];
        });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function yaNotificada(id) {
        return !!leerNotificadas()[id];
    }

    function mostrarNotificacion(cita) {
        const minutos = Math.max(cita.minutes_until, 0);
        const titulo = minutos > 0 ? ('Cita en ' + minutos + ' min') : 'Cita a punto de empezar';
        const detalles = [cita.patient];
        if (cita.service) detalles.push(cita.service);
        if (cita.doctor) detalles.push('Dr(a). ' + cita.doctor);
        const cuerpo = detalles.join(' · ') + ' · ' + cita.time;

        if (window.Notification && Notification.permission === 'granted') {
            const notif = new Notification(titulo, {
                body: cuerpo,
                icon: LOGO,
                tag: 'mc-cita-' + cita.id,
            });
            notif.onclick = function () {
                window.focus();
                window.location.href = cita.url;
                notif.close();
            };
        } else if (window.Swal) {
            Swal.fire({
                title: titulo,
                text: cuerpo,
                icon: 'info',
                confirmButtonText: 'Ver cita',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = cita.url;
                }
            });
        }
    }

    function revisarCitas() {
        fetch(ENDPOINT, { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.ok ? res.json() : []; })
            .then(function (citas) {
                citas.forEach(function (cita) {
                    if (!yaNotificada(cita.id)) {
                        mostrarNotificacion(cita);
                        marcarNotificada(cita.id);
                    }
                });
            })
            .catch(function () { /* si falla, se reintenta en el próximo ciclo */ });
    }

    // Los navegadores bloquean el pedido de permiso si no hay una interacción
    // del usuario, así que lo pedimos apenas hace el primer clic en la página.
    function pedirPermiso() {
        if (window.Notification && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
    document.addEventListener('click', pedirPermiso, { once: true });

    revisarCitas();
    setInterval(revisarCitas, POLL_MS);
})();
</script>
@endcan
