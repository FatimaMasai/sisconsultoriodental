<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-cart-shopping text-gray-400 mr-1"></i>
            Nueva Compra
        </x-label>

        <a href="{{ route('admin.purchases.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchase-form">
        @csrf

        {{-- 1. Proveedor --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                1. Datos de la compra
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-label class="form-label">Proveedor</x-label>
                    <x-select name="supplier_id" class="rounded-lg w-full" required>
                        <option value="">Seleccione un proveedor</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->person->name }} {{ $supplier->person->last_name_father }} {{ $supplier->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        </div>

        {{-- 2. Productos --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <x-label class="text-black dark:text-white text-base font-semibold">
                    2. Productos
                </x-label>

                <button type="button" id="add-product" class="btn btn-gray rounded-lg text-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Añadir producto
                </button>
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-2">Producto</th>
                            <th scope="col" class="px-4 py-2 w-20">Cant.</th>
                            <th scope="col" class="px-4 py-2 w-28">Precio</th>
                            <th scope="col" class="px-4 py-2 w-28">Subtotal</th>
                            <th scope="col" class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>

                    <tbody id="products">
                        @php
                            // Si el formulario falló, cargamos lo que había.
                            // Caso contrario, dejamos una fila vacía para empezar.
                            $oldProducts = old('products', [['product_id' => '', 'quantity' => 1]]);
                        @endphp

                        @foreach ($oldProducts as $i => $oldProduct)
                            <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 product-row">
                                <td class="px-4 py-2">
                                    <x-select name="products[{{ $i }}][product_id]" class="rounded-lg w-full product-select" required>
                                        <option value="">Seleccione un producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                @selected($oldProduct['product_id'] == $product->id)>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="products[{{ $i }}][quantity]"
                                        class="product-quantity rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                                        min="1" value="{{ $oldProduct['quantity'] ?? 1 }}" required>
                                </td>
                                <td class="px-4 py-2 product-price text-gray-700 dark:text-gray-300">Bs. 0</td>
                                <td class="px-4 py-2 product-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" class="remove-product text-red-500 hover:text-red-700" title="Quitar producto">
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

            <div class="mb-4 md:w-1/2">
                <x-label class="form-label">Método de pago</x-label>
                <x-select name="payment_method" class="rounded-lg w-full">
                    <option value="Efectivo" @selected(old('payment_method') == 'Efectivo')>Efectivo</option>
                    <option value="Transferencia" @selected(old('payment_method') == 'Transferencia')>Transferencia</option>
                    <option value="QR" @selected(old('payment_method') == 'QR')>QR</option>
                </x-select>
            </div>

            <div>
                <x-label class="form-label">Monto pagado</x-label>
                <input type="text" id="amount-display" class="rounded-lg border-gray-300 shadow-sm w-full md:w-1/2 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-white" value="Bs. 0" readonly>
                <input type="hidden" name="amount" id="amount" value="{{ old('amount', 0) }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Se calcula automáticamente según los productos agregados. Las compras se registran al contado.</p>
            </div>

            <input type="hidden" name="payment_status" value="Contado">
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Registrar Compra
            </button>
        </div>
    </form>

    @push('js')
    <script>
        let productIndex = {{ count($oldProducts) }};
        let currentTotal = 0;

        // Recalcula el precio/subtotal de cada fila de productos y el total general.
        function updateTotal() {
            currentTotal = 0;

            document.querySelectorAll('.product-row').forEach(row => {
                const select = row.querySelector('.product-select');
                const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
                const option = select.selectedOptions[0];
                const price = option ? (parseFloat(option.getAttribute('data-price')) || 0) : 0;
                const subtotal = price * quantity;

                row.querySelector('.product-price').textContent = 'Bs. ' + price.toFixed();
                row.querySelector('.product-subtotal').textContent = 'Bs. ' + subtotal.toFixed();

                currentTotal += subtotal;
            });

            document.getElementById('total').textContent = 'Bs. ' + currentTotal.toFixed();

            // Las compras siempre son al contado: el monto pagado es siempre el total.
            document.getElementById('amount-display').value = 'Bs. ' + currentTotal.toFixed();
            document.getElementById('amount').value = currentTotal.toFixed(2);
        }

        // Agregar una nueva fila de producto a la tabla.
        document.getElementById('add-product').addEventListener('click', function () {
            const row = document.createElement('tr');
            row.classList.add('product-row', 'bg-white', 'dark:bg-gray-800', 'border-b', 'dark:border-gray-700');
            row.innerHTML = `
                <td class="px-4 py-2">
                    <select name="products[${productIndex}][product_id]" class="rounded-lg w-full product-select border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        <option value="">Seleccione un producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-2">
                    <input type="number" name="products[${productIndex}][quantity]" class="product-quantity rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full" min="1" value="1" required>
                </td>
                <td class="px-4 py-2 product-price text-gray-700 dark:text-gray-300">Bs. 0</td>
                <td class="px-4 py-2 product-subtotal font-medium text-gray-900 dark:text-white">Bs. 0</td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="remove-product text-red-500 hover:text-red-700" title="Quitar producto">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            document.getElementById('products').appendChild(row);
            productIndex++;
            updateTotal();
        });

        // Recalcular cuando cambia el producto o la cantidad de cualquier fila.
        document.getElementById('products').addEventListener('input', function (event) {
            if (event.target.classList.contains('product-quantity') || event.target.classList.contains('product-select')) {
                updateTotal();
            }
        });
        document.getElementById('products').addEventListener('change', function (event) {
            if (event.target.classList.contains('product-select')) {
                updateTotal();
            }
        });

        // Quitar una fila de producto (se deja siempre al menos una fila).
        document.getElementById('products').addEventListener('click', function (event) {
            const button = event.target.closest('.remove-product');
            if (!button) return;

            const rows = document.querySelectorAll('.product-row');
            if (rows.length <= 1) return;

            button.closest('.product-row').remove();
            updateTotal();
        });

        // Estado inicial al cargar la página.
        updateTotal();
    </script>
    @endpush
</x-admin-layout>
