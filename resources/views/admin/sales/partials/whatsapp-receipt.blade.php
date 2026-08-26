{{-- Plantilla oculta del comprobante de pago (se "dibuja" fuera de pantalla y se
     convierte a imagen con html2canvas al presionar "Compartir"), + la función
     JS que arma esa imagen y la comparte por WhatsApp como una foto, igual que
     el comprobante que mandan los bancos al hacer un depósito. --}}

<div id="wa-receipt-capture" style="position: fixed; top: 0; left: -9999px; z-index: -1;">
    <div style="background:#ffffff; font-family: Arial, Helvetica, sans-serif; width:360px; border-radius:16px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#2563eb,#0d9488); padding:24px 20px; text-align:center;">
            <img src="{{ asset('images/logo-icon.png') }}" crossorigin="anonymous" style="height:36px; margin-bottom:10px; filter: brightness(0) invert(1);">
            <div style="width:64px;height:64px;background:#ffffff;border-radius:50%;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 12.5L9.5 18L20 6" stroke="#16a34a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div style="color:#ffffff;font-size:13px;letter-spacing:.03em;">COMPROBANTE DE PAGO</div>
        </div>

        <div style="padding:22px 20px 14px; text-align:center;">
            <div id="wa-receipt-monto" style="font-size:32px;font-weight:800;color:#0f172a;">Bs. 0</div>
            <div id="wa-receipt-concepto" style="font-size:14px;color:#64748b;margin-top:4px;">&nbsp;</div>
        </div>

        <div style="border-top:1px dashed #cbd5e1; margin:0 20px;"></div>

        <div style="padding:16px 20px 4px; font-size:13px; color:#334155;">
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:#94a3b8;">Paciente</span>
                <span id="wa-receipt-paciente" style="font-weight:600;">&nbsp;</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:#94a3b8;">Comprobante</span>
                <span id="wa-receipt-comprobante" style="font-weight:600;">&nbsp;</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:#94a3b8;">Método</span>
                <span id="wa-receipt-metodo" style="font-weight:600;">&nbsp;</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:#94a3b8;">Fecha</span>
                <span id="wa-receipt-fecha" style="font-weight:600;">&nbsp;</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:#94a3b8;">Saldo pendiente</span>
                <span id="wa-receipt-saldo" style="font-weight:600;">&nbsp;</span>
            </div>
        </div>

        <div style="background:#f8fafc;text-align:center;padding:10px;font-size:11px;color:#94a3b8;">
            Mi Consulta &middot; Control para tu consultorio
        </div>
    </div>
</div>

@push('js')
<script>
    // Llena la plantilla oculta con los datos del pago y la convierte en una
    // imagen (canvas). La usan tanto "Compartir" como "Descargar".
    async function renderReceiptCanvas(payload) {
        const el = document.getElementById('wa-receipt-capture');
        const card = el.firstElementChild;

        document.getElementById('wa-receipt-monto').innerText = payload.monto;
        document.getElementById('wa-receipt-concepto').innerText = payload.concepto;
        document.getElementById('wa-receipt-paciente').innerText = payload.paciente;
        document.getElementById('wa-receipt-comprobante').innerText = payload.comprobante;
        document.getElementById('wa-receipt-metodo').innerText = payload.metodo;
        document.getElementById('wa-receipt-fecha').innerText = payload.fecha;
        document.getElementById('wa-receipt-saldo').innerText = payload.saldo;

        // Se posiciona dentro de la pantalla (necesario para que html2canvas
        // pueda "leerlo"), pero con opacidad 0 para que no se vea el parpadeo.
        el.style.left = '0px';
        el.style.opacity = '0';
        el.style.pointerEvents = 'none';

        try {
            return await html2canvas(card, { backgroundColor: '#ffffff', scale: 2, useCORS: true });
        } finally {
            el.style.left = '-9999px';
            el.style.opacity = '1';
        }
    }

    function downloadBlob(blob, filename) {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    async function shareReceiptAsImage(payload) {
        const canvas = await renderReceiptCanvas(payload);

        canvas.toBlob(async function (blob) {
            if (!blob) return;

            const filename = 'comprobante_' + payload.comprobante + '.png';
            const file = new File([blob], filename, { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        files: [file],
                        title: 'Comprobante de pago',
                        text: payload.mensaje,
                    });
                } catch (err) {
                    // el usuario cerró el cuadro de compartir, no hacer nada
                }
            } else {
                // El navegador no soporta compartir archivos (común en escritorio):
                // se descarga la imagen y se abre WhatsApp con el texto, para
                // que el usuario adjunte la imagen manualmente en el chat.
                downloadBlob(blob, filename);

                Swal.fire({
                    title: 'Comprobante descargado',
                    text: 'Tu navegador no permite compartir la imagen directamente. Se descargó el comprobante y ahora se abrirá WhatsApp: solo adjunta la imagen descargada en el chat.',
                    icon: 'info',
                }).then(() => {
                    window.open('https://wa.me/' + payload.telefono + '?text=' + encodeURIComponent(payload.mensaje), '_blank');
                });
            }
        }, 'image/png');
    }

    async function downloadReceiptAsImage(payload) {
        const canvas = await renderReceiptCanvas(payload);

        canvas.toBlob(function (blob) {
            if (!blob) return;
            downloadBlob(blob, 'comprobante_' + payload.comprobante + '.png');
        }, 'image/png');
    }
</script>
@endpush
