<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Compra
    </x-label>

    <form action="{{ route('admin.purchases.store') }}" method="POST">
        @csrf

        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">
            <div>
                <x-label class="form-label">Proveedor</x-label>
                <x-select name="supplier_id" class="w-full">
                    <option value="">Seleccione un proveedor</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                            {{ $supplier->person->name }} {{ $supplier->person->last_name_father }} {{ $supplier->person->last_name_mother }}
                        </option>
                    @endforeach
                </x-select>
            </div>
 
        </div>

        <div class="relative overflow-x-auto mb-6">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Producto</th>
                        <th scope="col" class="px-6 py-3">Cantidad</th>
                        <th scope="col" class="px-6 py-3">Precio</th>
                        <th scope="col" class="px-6 py-3">Subtotal</th>
                        <th scope="col" class="px-6 py-3">Acción</th>
                    </tr>
                </thead>
                <tbody id="products">
                    <tr class="bg-white dark:bg-gray-800 product-row">
                        <td class="px-6 py-4">
                            <x-select name="products[0][product_id]" class="form-control product-select">
                                <option value="">Seleccione un producto</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }} - Bs. {{ $product->price }}
                                    </option>
                                @endforeach
                            </x-select>
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" name="products[0][quantity]" class="form-control product-quantity rounded-lg" min="1" value="1">
                        </td>
                        <td class="px-6 py-4">
                            <span class="product-price">Bs. 0.00</span> <!-- Precio inicial como 0.00 -->
                        </td>
                        <td class="px-6 py-4">
                            <span class="product-subtotal">Bs. 0.00</span> <!-- Subtotal inicial como 0.00 -->
                        </td>
                        <td class="px-6 py-4">
                            <button type="button" class="btn btn-red text-sm remove-product mt-2">Quitar</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <th scope="row" class="px-6 py-3 text-lg">Total</th>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3 text-lg" id="total">Bs. 0.00</td> <!-- Total inicial como 0.00 -->
                        <td class="px-6 py-3"></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="add-product" class="btn btn-green text-sm mt-4">Añadir otro producto</button>
        </div>

        <div class="mb-4">
            <label for="payment_method">Método de Pago</label>
            <x-select name="payment_method" class="input-label" class="form-control">
                <option value="Efectivo" @selected(old('payment_method') == 'Efectivo')>Efectivo</option>
                <option value="Transferencia" @selected(old('payment_method') == 'Transferencia')>Transferencia</option>
                <option value="QR" @selected(old('payment_method') == 'QR')>QR</option>
            </x-select>
        </div>

        <div class="mb-4">
            <label for="amount">Monto Pagado</label>
            <input type="number" name="amount" id="amount" class="form-control rounded-lg" placeholder="Ingrese el monto pagado" min="0" value="0">
        </div>

        <div class="mb-4">
            <label for="payment_status">Forma de Pago:</label>
            <x-select name="payment_status" class="form-control">
                <option value="Contado">Contado</option>
            </x-select>
        </div>

        <div class="flex justify-end">
            <x-button>
                Registrar Venta
            </x-button>
        </div>

    </form>

    <script>
        let productIndex = 1;
        let total = 0;

        // Función para actualizar el total y los subtotales
        function updateTotal() {
            total = 0;
            const rows = document.querySelectorAll('.product-row');
            rows.forEach(row => {
                const quantity = row.querySelector('.product-quantity').value;
                const price = row.querySelector('.product-select').selectedOptions[0].getAttribute('data-price') || 0; // Si el precio es null, usa 0
                const subtotal = price * quantity;

                // Mostrar el precio en la tabla
                row.querySelector('.product-price').textContent = 'Bs. ' + parseFloat(price).toFixed(2);

                // Mostrar el subtotal en la tabla
                row.querySelector('.product-subtotal').textContent = 'Bs. ' + subtotal.toFixed(2);

                total += subtotal;
            });
            document.getElementById('total').textContent = 'Bs. ' + total.toFixed(2); // Actualizar el total
        }

        // Agregar un nuevo producto al formulario
        document.getElementById('add-product').addEventListener('click', function() {
            const productRow = document.createElement('tr');
            productRow.classList.add('product-row', 'bg-white', 'dark:bg-gray-800');
            productRow.innerHTML = `
                <td class="px-6 py-4">
                    <x-select name="products[${productIndex}][product_id]" class="form-control product-select">
                        <option value="">Seleccione un producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }} - Bs. {{ $product->price }}
                            </option>
                        @endforeach
                    </x-select>
                </td>
                <td class="px-6 py-4">
                    <input type="number" name="products[${productIndex}][quantity]" class="form-control product-quantity rounded-lg"  min="1" value="1">
                </td>
                <td class="px-6 py-4">
                    <span class="product-price">Bs. 0.00</span>
                </td>
                <td class="px-6 py-4">
                    <span class="product-subtotal">Bs. 0.00</span>
                </td>
                <td class="px-6 py-4">
                    <button type="button" class="btn btn-red text-sm remove-product mt-2">Quitar</button>
                </td>
            `;
            document.getElementById('products').appendChild(productRow);
            productIndex++;
            updateTotal(); // Actualizamos el total después de agregar el producto
        });

        // Escuchar cambios en la cantidad y el producto
        document.getElementById('products').addEventListener('input', function(event) {
            if (event.target.classList.contains('product-quantity') || event.target.classList.contains('product-select')) {
                updateTotal();
            }
        });

        // Quitar un producto
        document.getElementById('products').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-product')) {
                event.target.closest('.product-row').remove();
                updateTotal(); // Recalcular el total después de quitar un producto
            }
        });

        // Inicializar el total en la carga de la página
        updateTotal();
    </script>
</x-admin-layout>
