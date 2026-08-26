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

    <form action="{{ route('admin.sales.store') }}" method="POST" id="sale-form">
        @csrf

        {{-- 1. Paciente y Doctor --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                1. Datos de la venta
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-label class="form-label">Paciente</x-label>
                    <x-select name="patient_id" class="rounded-lg w-full" required>
                        <option value="">Seleccione un paciente</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label class="form-label">Doctor</x-label>
                    <x-select name="doctor_id" class="rounded-lg w-full" required>
                        <option value="">Seleccione un doctor</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                {{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
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

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
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
                            <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 service-row">
                                <td class="px-4 py-2">
                                    <x-select name="services[{{ $i }}][service_id]" class="rounded-lg w-full service-select" required>
                                        <option value="">Seleccione un servicio</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-name="{{ $service->name }}"
                                                @selected($oldService['service_id'] == $service->id)>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="services[{{ $i }}][quantity]"
                                        class="service-quantity rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                                        min="1" value="{{ $oldService['quantity'] ?? 1 }}" required>
                                </td>
                                <td class="px-4 py-2 service-price text-gray-700 dark:text-gray-300">Bs. 0</td>
                                <td class="px-4 py-2 service-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" class="remove-service text-red-500 hover:text-red-700" title="Quitar servicio">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
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
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cuota inicial + cuotas mensuales</p>
                    </div>
                </label>
            </div>

            <div class="mb-4 md:w-1/2">
                <x-label class="form-label">Método de pago</x-label>
                <x-select name="payment_method" class="rounded-lg w-full">
                    <option value="Efectivo" @selected(old('payment_method') == 'Efectivo')>Efectivo</option>
                    <option value="Transferencia" @selected(old('payment_method') == 'Transferencia')>Transferencia</option>
                    <option value="QR" @selected(old('payment_method') == 'QR')>QR</option>
                </x-select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="payment-method-hint">
                    Con qué paga el paciente hoy.
                </p>
            </div>

            {{-- Solo Contado --}}
            <div id="contado-fields">
                <x-label class="form-label">Monto a pagar</x-label>
                <input type="text" id="amount-display" class="rounded-lg border-gray-300 shadow-sm w-full md:w-1/2 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-white" value="Bs. 0" readonly>
                <input type="hidden" name="amount" id="amount" value="{{ old('amount', 0) }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Se calcula automáticamente según los servicios agregados.</p>
            </div>

            {{-- Solo Credito --}}
            <div id="credito-fields" class="hidden">
                <div class="grid gap-4 md:grid-cols-2 mb-4">
                    <div>
                        <x-label class="form-label">Cuota inicial (Bs.)</x-label>
                        <input type="number" name="initial_amount" id="initial_amount" min="0" step="0.01"
                            value="{{ old('initial_amount', 0) }}"
                            class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Puede ser 0 si el paciente no adelanta nada.</p>
                    </div>

                    <div>
                        <x-label class="form-label">Número de cuotas</x-label>
                        <input type="number" name="installments_count" id="installments_count" min="1" max="36"
                            value="{{ old('installments_count', 1) }}"
                            class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cuotas mensuales sobre el saldo restante.</p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-3 text-sm text-gray-700 dark:text-gray-300 md:w-1/2">
                    <div class="flex justify-between py-0.5">
                        <span>Total de la venta</span>
                        <strong id="credito-total">Bs. 0</strong>
                    </div>
                    <div class="flex justify-between py-0.5 hidden text-amber-700 dark:text-amber-400" id="credito-consulta-row">
                        <span>Consulta (se cobra de inmediato)</span>
                        <strong id="credito-consulta">Bs. 0</strong>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span>Cuota inicial</span>
                        <strong id="credito-inicial">Bs. 0</strong>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span>Saldo a financiar</span>
                        <strong id="credito-saldo">Bs. 0</strong>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 mt-1 pt-1.5">
                        <span>Cuota mensual estimada</span>
                        <strong id="credito-cuota" class="text-blue-600">Bs. 0</strong>
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
        let consultaTotal = 0; // suma de las filas cuyo servicio es "Consulta": se cobra siempre de inmediato

        // Recalcula el precio/subtotal de cada fila de servicios y el total general.
        function updateTotal() {
            currentTotal = 0;
            consultaTotal = 0;

            document.querySelectorAll('.service-row').forEach(row => {
                const select = row.querySelector('.service-select');
                const quantity = parseFloat(row.querySelector('.service-quantity').value) || 0;
                const option = select.selectedOptions[0];
                const price = option ? (parseFloat(option.getAttribute('data-price')) || 0) : 0;
                const name = option ? (option.getAttribute('data-name') || '').trim().toLowerCase() : '';
                const subtotal = price * quantity;

                row.querySelector('.service-price').textContent = 'Bs. ' + price.toFixed();
                row.querySelector('.service-subtotal').textContent = 'Bs. ' + subtotal.toFixed();

                currentTotal += subtotal;
                if (name === 'consulta') {
                    consultaTotal += subtotal;
                }
            });

            document.getElementById('total').textContent = 'Bs. ' + currentTotal.toFixed();

            // Venta al Contado: el monto a pagar siempre es el total, no se pide escribirlo.
            document.getElementById('amount-display').value = 'Bs. ' + currentTotal.toFixed();
            document.getElementById('amount').value = currentTotal.toFixed(2);

            updateCreditoSummary();
        }

        // Agregar una nueva fila de servicio a la tabla.
        document.getElementById('add-service').addEventListener('click', function () {
            const row = document.createElement('tr');
            row.classList.add('service-row', 'bg-white', 'dark:bg-gray-800', 'border-b', 'dark:border-gray-700');
            row.innerHTML = `
                <td class="px-4 py-2">
                    <select name="services[${serviceIndex}][service_id]" class="rounded-lg w-full service-select border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        <option value="">Seleccione un servicio</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-name="{{ $service->name }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-2">
                    <input type="number" name="services[${serviceIndex}][quantity]" class="service-quantity rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full" min="1" value="1" required>
                </td>
                <td class="px-4 py-2 service-price text-gray-700 dark:text-gray-300">Bs. 0</td>
                <td class="px-4 py-2 service-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="remove-service text-red-500 hover:text-red-700" title="Quitar servicio">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            document.getElementById('services').appendChild(row);
            serviceIndex++;
            updateTotal();
        });

        // Recalcular cuando cambia el servicio o la cantidad de cualquier fila.
        document.getElementById('services').addEventListener('input', function (event) {
            if (event.target.classList.contains('service-quantity') || event.target.classList.contains('service-select')) {
                updateTotal();
            }
        });
        document.getElementById('services').addEventListener('change', function (event) {
            if (event.target.classList.contains('service-select')) {
                updateTotal();
            }
        });

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
        }

        function updatePaymentMethodHint() {
            const isCredito = document.querySelector('input[name="payment_type"]:checked')?.value === 'Credito';
            const hint = document.getElementById('payment-method-hint');

            if (!isCredito) {
                hint.textContent = 'Con qué paga el paciente hoy.';
            } else if (consultaTotal > 0) {
                hint.textContent = 'Con qué paga la Consulta hoy (y la cuota inicial, si corresponde).';
            } else {
                hint.textContent = 'Con qué paga la cuota inicial (si es mayor a 0).';
            }
        }

        function updateCreditoSummary() {
            const initial = parseFloat(document.getElementById('initial_amount').value) || 0;
            const count = parseInt(document.getElementById('installments_count').value) || 0;

            // La Consulta se cobra siempre de inmediato, no entra al monto a financiar.
            const financiable = Math.max(currentTotal - consultaTotal, 0);
            const saldo = Math.max(financiable - initial, 0);
            const cuota = count > 0 ? saldo / count : 0;

            document.getElementById('credito-total').textContent = 'Bs. ' + currentTotal.toFixed();
            document.getElementById('credito-inicial').textContent = 'Bs. ' + initial.toFixed();
            document.getElementById('credito-saldo').textContent = 'Bs. ' + saldo.toFixed();
            document.getElementById('credito-cuota').textContent = 'Bs. ' + cuota.toFixed();

            const consultaRow = document.getElementById('credito-consulta-row');
            if (consultaTotal > 0) {
                consultaRow.classList.remove('hidden');
                document.getElementById('credito-consulta').textContent = 'Bs. ' + consultaTotal.toFixed();
            } else {
                consultaRow.classList.add('hidden');
            }

            updatePaymentMethodHint();
        }

        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', togglePaymentType);
        });
        document.getElementById('initial_amount').addEventListener('input', updateCreditoSummary);
        document.getElementById('installments_count').addEventListener('input', updateCreditoSummary);

        // Estado inicial al cargar la página.
        updateTotal();
        togglePaymentType();
    </script>
    @endpush
</x-admin-layout>
