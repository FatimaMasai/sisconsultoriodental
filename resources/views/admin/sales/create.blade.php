<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Venta
    </x-label>

    <form action="{{ route('admin.sales.store') }}" method="POST">
        @csrf

        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">
            <div>
                <x-label class="form-label">Paciente</x-label>
                <x-select name="patient_id" class="w-full" >
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
                <x-select name="doctor_id" class="w-full" >
                    <option value="">Seleccione un doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                            {{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="relative overflow-x-auto mb-6">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Servicio</th>
                        <th scope="col" class="px-6 py-3">Cantidad</th>
                        <th scope="col" class="px-6 py-3">Precio</th>
                        <th scope="col" class="px-6 py-3">Subtotal</th>
                        <th scope="col" class="px-6 py-3">Acción</th>
                    </tr>
                </thead>
                <tbody id="services">
                    <tr class="bg-white dark:bg-gray-800 service-row">
                        <td class="px-6 py-4">
                            <x-select name="services[0][service_id]" class="form-control service-select" >
                                <option value="">Seleccione un servicio</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" >
                                        {{ $service->name }} - Bs. {{ $service->price }}
                                    </option>
                                @endforeach
                            </x-select>
                        </td>
                        <td class="px-6 py-4 ">
                            <input type="number" name="services[0][quantity]" class="form-control service-quantity rounded-lg"  min="1" value="1">
                        </td>
                        <td class="px-6 py-4">
                            <span class="service-price">Bs. 0</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="service-subtotal">Bs. 0</span>
                        </td>
                        <td class="px-6 py-4">
                            <button type="button" class="btn btn-red text-sm remove-service mt-2">Quitar</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <th scope="row" class="px-6 py-3 text-lg">Total</th>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3 text-lg" id="total">Bs. 0</td>
                        <td class="px-6 py-3"></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="add-service" class="btn btn-green text-sm mt-4">Añadir otro servicio</button>
        </div>

        {{-- <div class="mb-4">
            <label>Total a Pagar</label>
            <input type="text" id="total" class="form-control" value="Bs. 0" readonly>
        </div> --}}

       

        <div class="mb-4">
            <label for="payment_method">Método de Pago</label>
            <x-select name="payment_method" class="input-label" class="form-control ">

                <option value="efectivo" @selected(old('payment_method') == 'efectivo')> 
                    Efectivo
                </option>

                <option value="transferencia" @selected(old('payment_method') == 'transferencia')>
                    Transferencia
                </option>

                <option value="qr" @selected(old('payment_method') == 'qr')>
                    QR
                </option>

            </x-select>

           
        </div>

        <div class="mb-4">
            <label for="amount">Monto Pagado</label>
            <input type="number" name="amount" id="amount" class="form-control rounded-lg" placeholder="Ingrese el monto pagado" min="0" value="0" >
        </div>

        <div class="mb-4">
            <label for="payment_status">Forma de Pago:</label>
            <x-select name="payment_status" class="form-control" >
                <option value="contado">Contado</option>
                {{-- <option value="pendiente">Pendiente</option> --}}
            </x-select>
        </div>

        
        {{-- <x-button type="submit" class="btn btn-success">Registrar Venta</x-button> --}}

        <div class="flex justify-end">
            <x-button>
                Registrar Venta
            </x-button>
        </div>

    </form>

    <script>
        let serviceIndex = 1;
        let total = 0;

        // Función para actualizar el total y los subtotales
        function updateTotal() {
            total = 0;
            const rows = document.querySelectorAll('.service-row');
            rows.forEach(row => {
                const quantity = row.querySelector('.service-quantity').value;
                const price = row.querySelector('.service-select').selectedOptions[0].getAttribute('data-price');
                const subtotal = price * quantity;

                // Mostrar el precio en la tabla
                row.querySelector('.service-price').textContent = 'Bs. ' + price;

                // Mostrar el subtotal en la tabla
                row.querySelector('.service-subtotal').textContent = 'Bs. ' + subtotal.toFixed(2);

                total += subtotal;
            });
            document.getElementById('total').textContent = 'Bs. ' + total.toFixed(2); // Actualizar el total
        }

        // Agregar un nuevo servicio al formulario
        document.getElementById('add-service').addEventListener('click', function() {
            const serviceRow = document.createElement('tr');
            serviceRow.classList.add('service-row', 'bg-white', 'dark:bg-gray-800');
            serviceRow.innerHTML = `
                <td class="px-6 py-4">
                    <x-select name="services[${serviceIndex}][service_id]" class="form-control service-select" >
                        <option value="">Seleccione un servicio</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" data-price="{{ $service->price }}">
                                {{ $service->name }} - Bs. {{ $service->price }}
                            </option>
                        @endforeach
                    </x-select>
                </td>
                <td class="px-6 py-4">
                    <input type="number" name="services[${serviceIndex}][quantity]" class="form-control service-quantity rounded-lg"  min="1" value="1">
                </td>
                <td class="px-6 py-4">
                    <span class="service-price">Bs. 0</span>
                </td>
                <td class="px-6 py-4">
                    <span class="service-subtotal">Bs. 0</span>
                </td>
                <td class="px-6 py-4">
                    <button type="button" class="btn btn-red text-sm remove-service mt-2">Quitar</button>
                </td>
            `;
            document.getElementById('services').appendChild(serviceRow);
            serviceIndex++;
            updateTotal(); // Actualizamos el total después de agregar el servicio
        });

        // Escuchar cambios en la cantidad y el servicio
        document.getElementById('services').addEventListener('input', function(event) {
            if (event.target.classList.contains('service-quantity') || event.target.classList.contains('service-select')) {
                updateTotal();
            }
        });

        // Quitar un servicio
        document.getElementById('services').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-service')) {
                event.target.closest('.service-row').remove();
                updateTotal(); // Recalcular el total después de Quitar un servicio
            }
        });

        // Inicializar el total en la carga de la página
        updateTotal();
    </script>
</x-admin-layout>
