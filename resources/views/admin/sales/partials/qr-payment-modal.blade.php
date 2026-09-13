{{-- Modal reutilizable para el cobro por QR (VeriPagos). Se incluye una sola
     vez por página; se activa llamando a window.startQrPayment(form, monto, detalle). --}}
<div id="qr-payment-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-sm text-center shadow-xl">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">
            <i class="fa-solid fa-qrcode text-blue-500 mr-1"></i> Cobro por QR
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" id="qr-modal-amount">Bs. 0</p>

        <div id="qr-modal-loading" class="py-10">
            <i class="fa-solid fa-spinner fa-spin text-3xl text-blue-500"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Generando QR...</p>
        </div>

        <div id="qr-modal-image" class="hidden">
            <img id="qr-modal-img" src="" alt="Código QR" class="mx-auto w-56 h-56 border border-gray-200 dark:border-gray-600 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Esperando el pago del paciente...
            </p>
        </div>

        <div id="qr-modal-success" class="hidden py-10">
            <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-2"></i>
            <p class="text-sm text-gray-700 dark:text-gray-300">¡Pago recibido! Registrando la venta...</p>
        </div>

        <div id="qr-modal-error" class="hidden py-6 text-sm text-red-600 dark:text-red-400"></div>

        <button type="button" id="qr-modal-cancel" class="btn btn-gray rounded-lg text-sm mt-4">
            Cancelar
        </button>
    </div>
</div>

@once
    @push('js')
    <script>
        (function () {
            const modal = document.getElementById('qr-payment-modal');
            const loadingEl = document.getElementById('qr-modal-loading');
            const imageEl = document.getElementById('qr-modal-image');
            const imgTag = document.getElementById('qr-modal-img');
            const successEl = document.getElementById('qr-modal-success');
            const errorEl = document.getElementById('qr-modal-error');
            const amountEl = document.getElementById('qr-modal-amount');
            const cancelBtn = document.getElementById('qr-modal-cancel');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            let pollTimer = null;
            let activeForm = null;

            function resetModal() {
                loadingEl.classList.remove('hidden');
                imageEl.classList.add('hidden');
                successEl.classList.add('hidden');
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
                if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                resetModal();
                activeForm = null;
            }

            cancelBtn.addEventListener('click', closeModal);

            async function generarQr(monto, detalle) {
                const response = await fetch('{{ route('admin.veripagos.qr.generar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ monto: monto, detalle: detalle }),
                });
                const body = await response.json();
                if (!response.ok || !body.ok) {
                    throw new Error(body.message || 'No se pudo generar el QR.');
                }
                return body;
            }

            async function verificarEstado(movimientoId) {
                const response = await fetch(`{{ url('admin/veripagos/qr') }}/${movimientoId}/estado`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const body = await response.json();
                if (!response.ok || !body.ok) {
                    throw new Error(body.message || 'No se pudo verificar el estado del QR.');
                }
                return body;
            }

            // Punto de entrada público. `form` es el <form> que se debe enviar
            // automáticamente en cuanto el QR quede pagado.
            window.startQrPayment = function (form, monto, detalle) {
                activeForm = form;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                resetModal();
                amountEl.textContent = 'Bs. ' + Number(monto).toFixed(2);

                generarQr(monto, detalle).then(({ movimiento_id, qr }) => {
                    loadingEl.classList.add('hidden');
                    imageEl.classList.remove('hidden');
                    imgTag.src = qr;

                    pollTimer = setInterval(async () => {
                        try {
                            const estado = await verificarEstado(movimiento_id);
                            if (estado.estado === 'Completado') {
                                clearInterval(pollTimer);
                                pollTimer = null;
                                imageEl.classList.add('hidden');
                                successEl.classList.remove('hidden');

                                let hiddenInput = activeForm.querySelector('input[name="qr_movimiento_id"]');
                                if (!hiddenInput) {
                                    hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'qr_movimiento_id';
                                    activeForm.appendChild(hiddenInput);
                                }
                                hiddenInput.value = movimiento_id;

                                setTimeout(() => {
                                    closeModal();
                                    activeForm.submit();
                                }, 900);
                            }
                        } catch (e) {
                            // Fallo puntual de red al consultar: no se detiene el sondeo,
                            // solo se registra para no interrumpir la espera del cajero.
                            console.warn('Error verificando estado de QR:', e);
                        }
                    }, 4000);
                }).catch((e) => {
                    loadingEl.classList.add('hidden');
                    errorEl.classList.remove('hidden');
                    errorEl.textContent = e.message;
                });
            };
        })();
    </script>
    @endpush
@endonce
