<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-file-invoice-dollar text-gray-400 mr-1"></i>
            Nueva Venta
        </x-label>

        <a href="{{ route('admin.sales.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    @include('admin.sales.partials.qr-payment-modal')

    @php
        // Muestra el monto sin ceros de más (Bs 900, no Bs 900.00), pero
        // conserva los centavos si el precio realmente los tiene (Bs 150.5).
        $formatMoney = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    @endphp

    {{-- Viene de "Cobrar pendientes" en el Odontograma: se muestran los
         tratamientos que se están por cobrar y el total sugerido, para que
         quien carga la venta sepa qué Servicios elegir sin tener que
         mirar otra pantalla o un papel aparte. --}}
    @if ($pendingToothTreatments->isNotEmpty())
        <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-2 block">
                <i class="fa-solid fa-tooth text-blue-400 mr-1"></i>
                Tratamientos pendientes de cobro (del Odontograma)
            </x-label>

            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 mb-2">
                @foreach ($pendingToothTreatments as $pending)
                    <li>
                        {{ $pending->tooth_number ? 'Pieza ' . $pending->tooth_number . ' — ' : '' }}{{ $pending->treatment }}
                        @if ($pending->price !== null)
                            <span class="text-gray-500 dark:text-gray-400">(Bs {{ $formatMoney($pending->price) }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                Total sugerido: Bs {{ $formatMoney($pendingToothTreatments->sum('price')) }}
            </p>

            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Elegí abajo el o los Servicios que correspondan a estos tratamientos. Al guardar la venta, quedan marcados como cobrados en el Odontograma.
            </p>
        </div>
    @endif

    <form action="{{ route('admin.sales.store') }}" method="POST" id="sale-form">
        @csrf

        @foreach ($pendingToothTreatments as $pending)
            <input type="hidden" name="tooth_treatment_ids[]" value="{{ $pending->id }}">
        @endforeach

        {{-- 1. Paciente y Doctor --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                1. Datos de la venta
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                @php
                    // "old" (formulario que falló) tiene prioridad; si no, se
                    // usa el paciente que llegó por la URL (ej: desde
                    // "Cobrar pendientes" en el Odontograma).
                    $prefilledPatientId = old('patient_id', request('patient_id'));
                    $selectedPatient = $prefilledPatientId ? $patients->firstWhere('id', (int) $prefilledPatientId) : null;
                    $selectedDoctor = old('doctor_id') ? $doctors->firstWhere('id', (int) old('doctor_id')) : null;
                @endphp

                <div>
                    <x-label class="form-label">Paciente</x-label>
                    <div class="relative" data-person-combobox data-role="patient">
                        <input type="hidden" name="patient_id" class="person-search-hidden" value="{{ $selectedPatient?->id }}">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" autocomplete="off" class="person-search-input input-label rounded-lg pl-9 w-full"
                                placeholder="Buscar paciente por nombre o apellido..."
                                value="{{ $selectedPatient ? $selectedPatient->person->name . ' ' . $selectedPatient->person->last_name_father . ' ' . $selectedPatient->person->last_name_mother : '' }}">
                        </div>
                        <div class="person-search-results hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                            @foreach ($patients as $patient)
                                <button type="button" class="person-option w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-200"
                                    data-id="{{ $patient->id }}"
                                    data-name="{{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}">
                                    {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                                </button>
                            @endforeach
                            <p class="person-empty hidden px-3 py-2 text-sm text-gray-400">Sin resultados</p>
                            <p class="person-more hidden px-3 py-2 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <x-label class="form-label">Doctor</x-label>
                    <div class="relative" data-person-combobox data-role="doctor">
                        <input type="hidden" name="doctor_id" class="person-search-hidden" value="{{ old('doctor_id') }}">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" autocomplete="off" class="person-search-input input-label rounded-lg pl-9 w-full"
                                placeholder="Buscar doctor por nombre o apellido..."
                                value="{{ $selectedDoctor ? $selectedDoctor->person->name . ' ' . $selectedDoctor->person->last_name_father . ' ' . $selectedDoctor->person->last_name_mother : '' }}">
                        </div>
                        <div class="person-search-results hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                            @foreach ($doctors as $doctor)
                                <button type="button" class="person-option w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-200"
                                    data-id="{{ $doctor->id }}"
                                    data-name="{{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}">
                                    {{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}
                                </button>
                            @endforeach
                            <p class="person-empty hidden px-3 py-2 text-sm text-gray-400">Sin resultados</p>
                            <p class="person-more hidden px-3 py-2 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Servicios --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <x-label class="text-black dark:text-white text-base font-semibold">
                    2. Servicios
                </x-label>

                <button type="button" id="add-service" class="btn btn-gray rounded-lg text-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Añadir servicio
                </button>
            </div>

            {{-- Sin "overflow-x-auto": la tabla ya se apila sola en mobile
                 (ver table-stack.css) y este wrapper cortaba el listado del
                 buscador de Servicio cuando se abría cerca del borde. --}}
            <div class="relative">
                <table class="table-stack w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-2">Servicio</th>
                            <th scope="col" class="px-4 py-2 w-20">Cant.</th>
                            <th scope="col" class="px-4 py-2 w-28">Precio</th>
                            <th scope="col" class="px-4 py-2 w-28">Subtotal</th>
                            <th scope="col" class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>

                    <tbody id="services">
                        @php
                            // Si el formulario falló, cargamos lo que había.
                            // Caso contrario, dejamos una fila vacía para empezar.
                            $oldServices = old('services', [['service_id' => '', 'quantity' => 1]]);
                        @endphp

                        @foreach ($oldServices as $i => $oldService)
                            @php
                                $matchedService = $services->firstWhere('id', (int) ($oldService['service_id'] ?? 0));
                                $oldPrice = $oldService['price'] ?? optional($matchedService)->price;
                            @endphp
                            <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 service-row">
                                <td data-label="Servicio" class="px-4 py-2">
                                    <div class="relative" data-service-combobox>
                                        <input type="hidden" name="services[{{ $i }}][service_id]" class="service-search-hidden" value="{{ $oldService['service_id'] ?? '' }}">
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                            <input type="text" autocomplete="off" class="service-search-input input-label rounded-lg pl-9 w-full"
                                                placeholder="Buscar servicio..."
                                                value="{{ $matchedService?->name }}">
                                        </div>
                                        <div class="service-search-results hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                            @foreach ($services as $service)
                                                <button type="button" class="service-option w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-200"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-price="{{ $service->price }}">
                                                    {{ $service->name }}
                                                </button>
                                            @endforeach
                                            <p class="service-empty hidden px-3 py-2 text-sm text-gray-400">Sin resultados</p>
                                            <p class="service-more hidden px-3 py-2 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700"></p>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Cant." class="px-4 py-2">
                                    <input type="number" name="services[{{ $i }}][quantity]"
                                        class="service-quantity input-label rounded-lg w-full"
                                        min="1" value="{{ $oldService['quantity'] ?? 1 }}" required>
                                </td>
                                <td data-label="Precio" class="px-4 py-2">
                                    <input type="number" name="services[{{ $i }}][price]" step="0.01" min="0"
                                        class="service-price-input input-label rounded-lg w-full"
                                        value="{{ $oldPrice !== null ? (float) $oldPrice : '' }}" placeholder="Bs. 0">
                                </td>
                                <td data-label="Subtotal" class="px-4 py-2 service-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" class="remove-service text-red-500 hover:text-red-700" title="Quitar servicio">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/40">
                            <td class="px-4 py-2" colspan="3">Subtotal</td>
                            <td class="px-4 py-2" id="subtotal">Bs. 0</td>
                            <td></td>
                        </tr>
                        <tr class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/40">
                            <td class="px-4 py-2" colspan="3">
                                <label for="discount-input" class="cursor-pointer">Descuento (Bs.)</label>
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" name="discount" id="discount-input" min="0" step="0.01"
                                    value="{{ old('discount', 0) }}" placeholder="Bs. 0"
                                    class="input-label rounded-lg w-full">
                            </td>
                            <td></td>
                        </tr>
                        <tr class="font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/40">
                            <td class="px-4 py-3" colspan="3">Total</td>
                            <td class="px-4 py-3 text-lg" id="total">Bs. 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 3. Forma de pago --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                3. Forma de pago
            </x-label>

            <div class="grid grid-cols-2 gap-3 mb-4 max-w-md">
                <label class="cursor-pointer">
                    <input type="radio" name="payment_type" value="Contado" class="peer sr-only"
                        @checked(old('payment_type', 'Contado') == 'Contado')>
                    <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/10">
                        <i class="fa-solid fa-money-bill-wave text-gray-400 mb-1"></i>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Contado</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pago completo hoy</p>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="payment_type" value="Credito" class="peer sr-only"
                        @checked(old('payment_type') == 'Credito')>
                    <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/10">
                        <i class="fa-solid fa-calendar-days text-gray-400 mb-1"></i>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Crédito</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Abono libre, se paga cuando pueda</p>
                    </div>
                </label>
            </div>

            <div class="mb-4 md:w-1/2">
                <x-label class="form-label">Método de pago</x-label>
                <x-select name="payment_method" class="input-label rounded-lg w-full">
                    <option value="Efectivo" @selected(old('payment_method') == 'Efectivo')>Efectivo</option>
                    <option value="Transferencia" @selected(old('payment_method') == 'Transferencia')>Transferencia</option>
                    <option value="QR" @selected(old('payment_method') == 'QR')>QR</option>
                </x-select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="payment-method-hint">
                    Con qué paga el paciente hoy.
                </p>
            </div>

            <input type="hidden" name="amount" id="amount" value="{{ old('amount', 0) }}">

            {{-- Solo Contado --}}
            <div id="contado-fields">
                <x-label class="form-label">Monto a pagar</x-label>
                <input type="text" id="amount-display" class="rounded-lg border-gray-300 shadow-sm w-full md:w-1/2 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-white" value="Bs. 0" readonly>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Se calcula automáticamente según los servicios agregados.</p>
            </div>

            {{-- Solo Credito --}}
            <div id="credito-fields" class="hidden">
                <div class="max-w-sm mb-4">
                    <x-label class="form-label">Abono de hoy (Bs.)</x-label>
                    <input type="number" id="credito-amount-input" min="0" step="0.01"
                        value="{{ old('amount', 0) }}"
                        class="input-label rounded-lg w-full">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Opcional. Déjelo en 0 si el paciente no adelanta nada hoy; los siguientes abonos se registran después desde el detalle de la venta.</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-3 text-sm text-gray-700 dark:text-gray-300 md:w-1/2">
                    <div class="flex justify-between py-0.5">
                        <span>Total de la venta</span>
                        <strong id="credito-total">Bs. 0</strong>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 mt-1 pt-1.5">
                        <span>Saldo pendiente después de este abono</span>
                        <strong id="credito-saldo" class="text-blue-600">Bs. 0</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.sales.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Registrar Venta
            </button>
        </div>
    </form>

    @push('js')
    <script>
        let serviceIndex = {{ count($oldServices) }};
        let currentTotal = 0;

        // Muestra el monto sin ceros de más (1100 en vez de 1100.00), pero
        // conserva los centavos si realmente existen (150.5).
        function formatMoney(n) {
            return Number(n.toFixed(2)).toString();
        }

        // Recalcula el subtotal de cada fila de servicios, le resta el
        // descuento (si hay uno cargado) y actualiza el total general. El
        // precio de cada fila es editable: por default es el precio de
        // lista del Servicio, pero se puede cambiar a mano si ese caso
        // puntual cuesta distinto.
        function updateTotal() {
            let subtotal = 0;

            document.querySelectorAll('.service-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.service-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.service-price-input').value) || 0;
                const rowSubtotal = price * quantity;

                row.querySelector('.service-subtotal').textContent = 'Bs. ' + formatMoney(rowSubtotal);

                subtotal += rowSubtotal;
            });

            document.getElementById('subtotal').textContent = 'Bs. ' + formatMoney(subtotal);

            // El descuento no puede dejar el total en negativo (si el
            // usuario escribe uno mayor al subtotal, acá solo se recorta
            // para mostrar; el servidor igual valida y rechaza ese caso).
            const discount = Math.min(parseFloat(document.getElementById('discount-input').value) || 0, subtotal);
            currentTotal = Math.max(subtotal - discount, 0);

            document.getElementById('total').textContent = 'Bs. ' + formatMoney(currentTotal);

            // Venta al Contado: el monto a pagar siempre es el total, no se pide escribirlo.
            document.getElementById('amount-display').value = 'Bs. ' + formatMoney(currentTotal);

            updateCreditoSummary();
            syncAmountField();
        }

        // Combobox de Servicio: mismo patrón que el buscador de Paciente/Doctor
        // de arriba (con "+N más" y límite de resultados visibles), pero uno
        // por fila. Sirve para elegir rápido aunque la lista crezca a futuro
        // (ya van +40 servicios). Se llama una vez por cada fila que exista
        // al cargar la página y de nuevo por cada fila que se agregue con
        // "Añadir servicio".
        function initServiceCombobox(wrapper) {
            const hidden = wrapper.querySelector('.service-search-hidden');
            const input = wrapper.querySelector('.service-search-input');
            const results = wrapper.querySelector('.service-search-results');
            const options = Array.from(wrapper.querySelectorAll('.service-option'));
            const empty = wrapper.querySelector('.service-empty');
            const more = wrapper.querySelector('.service-more');
            const MAX_VISIBLE = 8;

            function showResults() {
                const term = input.value.trim().toLowerCase();
                let matches = 0;
                let shown = 0;
                options.forEach(function (opt) {
                    const match = opt.dataset.name.toLowerCase().includes(term);
                    if (match) matches++;
                    const visible = match && shown < MAX_VISIBLE;
                    if (visible) shown++;
                    opt.classList.toggle('hidden', !visible);
                });

                empty.classList.toggle('hidden', matches > 0);

                const remaining = matches - shown;
                if (remaining > 0) {
                    more.textContent = `+${remaining} más, sigue escribiendo para ver otros`;
                    more.classList.remove('hidden');
                } else {
                    more.classList.add('hidden');
                }

                results.classList.remove('hidden');
            }

            input.addEventListener('focus', showResults);
            input.addEventListener('input', function () {
                hidden.value = '';
                showResults();
            });

            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    hidden.value = opt.dataset.id;
                    input.value = opt.dataset.name;
                    results.classList.add('hidden');

                    // Se completa el precio con el de lista; sigue editable después.
                    const row = wrapper.closest('.service-row');
                    const price = parseFloat(opt.dataset.price) || 0;
                    row.querySelector('.service-price-input').value = price;
                    updateTotal();
                });
            });
        }

        document.querySelectorAll('[data-service-combobox]').forEach(initServiceCombobox);

        // Un solo listener (no uno por fila) para cerrar el listado abierto
        // al hacer clic afuera; evita ir acumulando listeners si se agregan
        // muchas filas de servicio.
        document.addEventListener('click', function (e) {
            document.querySelectorAll('[data-service-combobox]').forEach(function (wrapper) {
                if (!wrapper.contains(e.target)) {
                    wrapper.querySelector('.service-search-results')?.classList.add('hidden');
                }
            });
        });

        // Agregar una nueva fila de servicio a la tabla.
        document.getElementById('add-service').addEventListener('click', function () {
            const row = document.createElement('tr');
            row.classList.add('service-row', 'bg-white', 'dark:bg-gray-800', 'border-b', 'dark:border-gray-700');
            row.innerHTML = `
                <td data-label="Servicio" class="px-4 py-2">
                    <div class="relative" data-service-combobox>
                        <input type="hidden" name="services[${serviceIndex}][service_id]" class="service-search-hidden" value="">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" autocomplete="off" class="service-search-input input-label rounded-lg pl-9 w-full" placeholder="Buscar servicio...">
                        </div>
                        <div class="service-search-results hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                            @foreach ($services as $service)
                                <button type="button" class="service-option w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-200"
                                    data-id="{{ $service->id }}"
                                    data-name="{{ $service->name }}"
                                    data-price="{{ $service->price }}">
                                    {{ $service->name }}
                                </button>
                            @endforeach
                            <p class="service-empty hidden px-3 py-2 text-sm text-gray-400">Sin resultados</p>
                            <p class="service-more hidden px-3 py-2 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700"></p>
                        </div>
                    </div>
                </td>
                <td data-label="Cant." class="px-4 py-2">
                    <input type="number" name="services[${serviceIndex}][quantity]" class="service-quantity input-label rounded-lg w-full" min="1" value="1" required>
                </td>
                <td data-label="Precio" class="px-4 py-2">
                    <input type="number" name="services[${serviceIndex}][price]" step="0.01" min="0" class="service-price-input input-label rounded-lg w-full" placeholder="Bs. 0">
                </td>
                <td data-label="Subtotal" class="px-4 py-2 service-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="remove-service text-red-500 hover:text-red-700" title="Quitar servicio">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            document.getElementById('services').appendChild(row);
            initServiceCombobox(row.querySelector('[data-service-combobox]'));
            serviceIndex++;
            updateTotal();
        });

        // Recalcular cuando cambia la cantidad o el precio de cualquier fila
        // (el precio es editable, así que también dispara el recálculo).
        document.getElementById('services').addEventListener('input', function (event) {
            if (event.target.classList.contains('service-quantity') || event.target.classList.contains('service-price-input')) {
                updateTotal();
            }
        });

        // Recalcular cuando se carga o cambia el descuento.
        document.getElementById('discount-input').addEventListener('input', updateTotal);

        // Quitar una fila de servicio (se deja siempre al menos una fila).
        document.getElementById('services').addEventListener('click', function (event) {
            const button = event.target.closest('.remove-service');
            if (!button) return;

            const rows = document.querySelectorAll('.service-row');
            if (rows.length <= 1) return;

            button.closest('.service-row').remove();
            updateTotal();
        });

        // --- Tipo de venta: Contado / Crédito ---
        const contadoFields = document.getElementById('contado-fields');
        const creditoFields = document.getElementById('credito-fields');

        function togglePaymentType() {
            const selected = document.querySelector('input[name="payment_type"]:checked')?.value ?? 'Contado';
            const isCredito = selected === 'Credito';

            contadoFields.classList.toggle('hidden', isCredito);
            creditoFields.classList.toggle('hidden', !isCredito);

            updateCreditoSummary();
            updatePaymentMethodHint();
            syncAmountField();
        }

        function updatePaymentMethodHint() {
            const isCredito = document.querySelector('input[name="payment_type"]:checked')?.value === 'Credito';
            const hint = document.getElementById('payment-method-hint');

            hint.textContent = isCredito
                ? 'Con qué paga el paciente el abono de hoy (si escribe un monto mayor a 0).'
                : 'Con qué paga el paciente hoy.';
        }

        function updateCreditoSummary() {
            const abono = parseFloat(document.getElementById('credito-amount-input').value) || 0;
            const saldo = Math.max(currentTotal - abono, 0);

            document.getElementById('credito-total').textContent = 'Bs. ' + formatMoney(currentTotal);
            document.getElementById('credito-saldo').textContent = 'Bs. ' + formatMoney(saldo);
        }

        // El monto que realmente se envía al servidor (campo oculto compartido
        // "amount") depende de la forma de pago elegida: en Contado siempre es
        // el total de la venta; en Credito es el abono libre que el usuario
        // escribió hoy (puede ser 0 si no adelanta nada).
        function syncAmountField() {
            const isCredito = document.querySelector('input[name="payment_type"]:checked')?.value === 'Credito';
            const amountField = document.getElementById('amount');

            if (isCredito) {
                const abono = parseFloat(document.getElementById('credito-amount-input').value) || 0;
                amountField.value = abono.toFixed(2);
            } else {
                amountField.value = currentTotal.toFixed(2);
            }
        }

        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', togglePaymentType);
        });
        document.getElementById('credito-amount-input').addEventListener('input', function () {
            updateCreditoSummary();
            syncAmountField();
        });

        // Estado inicial al cargar la página.
        updateTotal();
        togglePaymentType();

        // --- Buscador de Paciente / Doctor: filtra la lista mientras se escribe ---
        // (mismo patrón que "Seleccionar persona" en Pacientes > Nuevo paciente).
        document.querySelectorAll('[data-person-combobox]').forEach(function (wrapper) {
            const input = wrapper.querySelector('.person-search-input');
            const hidden = wrapper.querySelector('.person-search-hidden');
            const results = wrapper.querySelector('.person-search-results');
            const options = Array.from(wrapper.querySelectorAll('.person-option'));
            const empty = wrapper.querySelector('.person-empty');
            const more = wrapper.querySelector('.person-more');
            const MAX_VISIBLE = 8;

            function showResults() {
                const term = input.value.trim().toLowerCase();
                let matches = 0;
                let shown = 0;
                options.forEach(function (opt) {
                    const match = opt.dataset.name.toLowerCase().includes(term);
                    if (match) matches++;
                    const visible = match && shown < MAX_VISIBLE;
                    if (visible) shown++;
                    opt.classList.toggle('hidden', !visible);
                });

                empty.classList.toggle('hidden', matches > 0);

                const remaining = matches - shown;
                if (remaining > 0) {
                    more.textContent = `+${remaining} más, sigue escribiendo para ver otras`;
                    more.classList.remove('hidden');
                } else {
                    more.classList.add('hidden');
                }

                results.classList.remove('hidden');
            }

            input.addEventListener('focus', showResults);
            input.addEventListener('input', function () {
                hidden.value = '';
                showResults();
            });

            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    hidden.value = opt.dataset.id;
                    input.value = opt.dataset.name;
                    results.classList.add('hidden');
                });
            });

            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    results.classList.add('hidden');
                }
            });
        });

        // Nota: "QR" es una opción más de método de pago, igual que Efectivo o
        // Transferencia. No dispara ninguna llamada a la API de VeriPagos ni
        // abre el modal de QR: el formulario se envía normalmente y el pago
        // queda registrado con payment_method = 'QR' sin verificación alguna.
        const saleForm = document.getElementById('sale-form');

        saleForm.addEventListener('submit', function (event) {
            // El paciente y el doctor ahora se eligen con el buscador (no un <select>
            // nativo), así que el "required" del HTML ya no valida esto por sí solo.
            const patientId = saleForm.querySelector('[name="patient_id"]').value;
            const doctorId = saleForm.querySelector('[name="doctor_id"]').value;

            if (!patientId) {
                event.preventDefault();
                alert('Debe seleccionar un paciente.');
                document.querySelector('[data-role="patient"] .person-search-input')?.focus();
                return;
            }
            if (!doctorId) {
                event.preventDefault();
                alert('Debe seleccionar un doctor.');
                document.querySelector('[data-role="doctor"] .person-search-input')?.focus();
                return;
            }

            // Igual que Paciente/Doctor: el Servicio ahora se elige con el
            // buscador (no un <select> nativo), así que se valida acá que
            // cada fila tenga uno elegido.
            for (const wrapper of document.querySelectorAll('[data-service-combobox]')) {
                const serviceHidden = wrapper.querySelector('.service-search-hidden');
                if (!serviceHidden.value) {
                    event.preventDefault();
                    alert('Debe seleccionar un servicio en cada fila.');
                    wrapper.querySelector('.service-search-input')?.focus();
                    return;
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
