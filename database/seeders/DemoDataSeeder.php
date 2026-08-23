<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\History;
use App\Models\Installment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Speciality;
use App\Models\Supplier;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

/**
 * Seeder de datos de demostración para ver la aplicación en funcionamiento:
 * pacientes, doctores, proveedores, compras (algunas anuladas) y ventas
 * -tanto al Contado como a Crédito, con cuotas en distintos estados
 * (pagadas, pendientes, vencidas)- para que el dashboard, el listado de
 * ventas/compras y el plan de cuotas tengan datos reales que mostrar.
 *
 * Pensado para correr UNA sola vez sobre una base de datos de prueba:
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Los catálogos (especialidades, categorías, servicios, productos) usan
 * firstOrCreate y no se duplican si vuelves a correrlo. Pacientes, doctores,
 * proveedores, compras y ventas SÍ se agregan de nuevo cada vez que lo
 * corres (no es idempotente para esa parte) -si quieres repetirlo desde
 * cero, vacía esas tablas antes o usa una base de datos aparte para pruebas.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // 1. Catálogos base (no se duplican si ya existen)
        foreach (['Odontología General', 'Ortodoncia', 'Endodoncia', 'Odontopediatría', 'Estética Dental'] as $name) {
            Speciality::firstOrCreate(['name' => $name], ['status' => true]);
        }

        $serviceCategories = [
            'Limpieza' => [
                ['name' => 'Limpieza Dental', 'price' => 50],
                ['name' => 'Aplicación de Flúor', 'price' => 100],
            ],
            'Ortodoncia' => [
                ['name' => 'Brackets Metálicos', 'price' => 700],
            ],
            'Endodoncia' => [
                ['name' => 'Endodoncia (Tratamiento de Conducto)', 'price' => 200],
            ],
            'Odontopediatría' => [
                ['name' => 'Consulta Infantil', 'price' => 90],
            ],
            'Estética Dental' => [
                ['name' => 'Blanqueamiento Dental', 'price' => 900],
            ],
            'Cirugía' => [
                ['name' => 'Extracción de Muelas del Juicio', 'price' => 120],
            ],
            'Radiología' => [
                ['name' => 'Radiografía Panorámica', 'price' => 70],
            ],
        ];

        foreach ($serviceCategories as $categoryName => $services) {
            $category = ServiceCategory::firstOrCreate(['name' => $categoryName], ['status' => true]);

            foreach ($services as $service) {
                Service::firstOrCreate(
                    ['name' => $service['name']],
                    ['price' => $service['price'], 'status' => true, 'service_category_id' => $category->id]
                );
            }
        }

        $productCategories = [
            'Material de Restauración' => [['name' => 'Resina Dental Filtek Z350 XT', 'price' => 500]],
            'Instrumental Quirúrgico' => [['name' => 'Forceps de extracción dental', 'price' => 300]],
            'Material de Esterilización' => [['name' => 'Bolsas de esterilización autoadhesivas', 'price' => 100]],
            'Equipos Dentales' => [['name' => 'Unidad dental con lámpara LED', 'price' => 25500]],
        ];

        foreach ($productCategories as $categoryName => $products) {
            $category = ProductCategory::firstOrCreate(['name' => $categoryName], ['status' => true]);

            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['name' => $product['name']],
                    ['price' => $product['price'], 'status' => true, 'product_category_id' => $category->id]
                );
            }
        }

        $specialityIds = Speciality::pluck('id')->toArray();
        $serviceIds = Service::where('status', 1)->pluck('id')->toArray();
        $productIds = Product::where('status', 1)->pluck('id')->toArray();

        $paymentMethods = ['Efectivo', 'Transferencia', 'QR'];

        $makePerson = function () use ($faker) {
            return Person::create([
                'name' => $faker->firstName,
                'last_name_father' => $faker->lastName,
                'last_name_mother' => $faker->lastName,
                'identity_card' => $faker->unique()->numerify('#######'),
                'birth_date' => $faker->date('Y-m-d', '2010-12-31'),
                'gender' => $faker->randomElement(['Femenino', 'Masculino']),
                'phone' => $faker->numerify('7#######'),
                'email' => $faker->unique()->safeEmail,
                'address' => $faker->address,
                'status' => true,
            ]);
        };

        // 2. Doctores (5)
        $doctorIds = [];
        for ($i = 0; $i < 5; $i++) {
            $person = $makePerson();

            $doctor = Doctor::create([
                'status' => true,
                'person_id' => $person->id,
                'speciality_id' => $faker->randomElement($specialityIds),
            ]);

            $doctorIds[] = $doctor->id;
        }

        // 3. Pacientes (15)
        $patientIds = [];
        for ($i = 0; $i < 15; $i++) {
            $person = $makePerson();

            $patient = Patient::create([
                'allergy' => $faker->randomElement(['Ninguna', 'Penicilina', 'Aspirina', 'Ibuprofeno', 'Lácteos']),
                'observation' => $faker->sentence(4),
                'recommended_by' => $faker->name,
                'responsible_person' => $faker->name,
                'medical_history' => $faker->sentence(3),
                'status' => true,
                'person_id' => $person->id,
            ]);

            $patientIds[] = $patient->id;
        }

        // 4. Proveedores (4)
        $supplierIds = [];
        for ($i = 0; $i < 4; $i++) {
            $person = $makePerson();

            $supplier = Supplier::create([
                'company' => $faker->company,
                'nit' => $faker->numerify('#######'),
                'status' => true,
                'person_id' => $person->id,
            ]);

            $supplierIds[] = $supplier->id;
        }

        // 5. Compras (12): la mayoría activas, ~1 de cada 5 anulada (para probar el dashboard)
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subDays(rand(0, 180));
            $isAnulada = $i % 5 === 0;

            $productsForPurchase = collect($productIds)->random(min(3, count($productIds)));

            $total = 0;
            $details = [];
            foreach ($productsForPurchase as $productId) {
                $product = Product::find($productId);
                $quantity = rand(1, 10);
                $subtotal = $product->price * $quantity;
                $total += $subtotal;
                $details[] = ['product_id' => $productId, 'price' => $product->price, 'quantity' => $quantity, 'subtotal' => $subtotal];
            }

            $purchase = Purchase::create([
                'date' => $date,
                'total' => $total,
                'status' => $isAnulada ? 0 : 1,
                'supplier_id' => $faker->randomElement($supplierIds),
            ]);
            $purchase->created_at = $date;
            $purchase->updated_at = $date;
            $purchase->save();

            foreach ($details as $detail) {
                PurchaseDetail::create([
                    'price' => $detail['price'],
                    'quantity' => $detail['quantity'],
                    'subtotal' => $isAnulada ? 0 : $detail['subtotal'],
                    'purchase_id' => $purchase->id,
                    'product_id' => $detail['product_id'],
                ]);
            }

            Payment::create([
                'amount' => $total,
                'payment_method' => $faker->randomElement($paymentMethods),
                'payment_status' => $isAnulada ? 'Anulado' : 'Contado',
                'purchase_id' => $purchase->id,
            ]);
        }

        // 6. Ventas (20): mezcla de Contado y Credito, con historial de servicios
        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::now()->subDays(rand(0, 180));
            $patientId = $faker->randomElement($patientIds);
            $doctorId = $faker->randomElement($doctorIds);

            $servicesForSale = collect($serviceIds)->random(min(rand(1, 2), count($serviceIds)));

            $total = 0;
            $saleDetails = [];
            foreach ($servicesForSale as $serviceId) {
                $service = Service::find($serviceId);
                $subtotal = $service->price; // cantidad = 1
                $total += $subtotal;
                $saleDetails[] = ['service_id' => $serviceId, 'price' => $service->price, 'subtotal' => $subtotal];
            }

            $isAnulada = $i % 7 === 0; // ~15% anuladas
            $isCredito = ! $isAnulada && in_array($i % 5, [2, 4], true); // resto: ~40% a Credito

            $paymentType = $isCredito ? 'Credito' : 'Contado';

            $sale = Sale::create([
                'sale_date' => $date,
                'total' => $total,
                'status' => $isAnulada ? 0 : 1,
                'payment_type' => $paymentType,
                'initial_amount' => 0,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
            ]);
            $sale->created_at = $date;
            $sale->updated_at = $date;
            $sale->save();

            foreach ($saleDetails as $detail) {
                SaleDetail::create([
                    'price' => $detail['price'],
                    'quantity' => 1,
                    'subtotal' => $isAnulada ? 0 : $detail['subtotal'],
                    'sale_id' => $sale->id,
                    'service_id' => $detail['service_id'],
                ]);

                History::create([
                    'description' => 'Venta de servicio',
                    'date' => $date,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'service_id' => $detail['service_id'],
                ]);
            }

            if ($paymentType === 'Contado') {
                Payment::create([
                    'amount' => $total,
                    'payment_method' => $faker->randomElement($paymentMethods),
                    'payment_status' => $isAnulada ? 'Anulado' : 'Contado',
                    'sale_id' => $sale->id,
                ]);

                continue;
            }

            // Venta a Credito: cuota inicial del 30% + entre 3 y 6 cuotas mensuales
            $initialAmount = round($total * 0.3, 2);
            $installmentsCount = rand(3, 6);

            $sale->update(['initial_amount' => $initialAmount]);

            Payment::create([
                'amount' => $initialAmount,
                'payment_method' => $faker->randomElement($paymentMethods),
                'payment_status' => $isAnulada ? 'Anulado' : 'Cuota Inicial',
                'sale_id' => $sale->id,
            ]);

            $saldoFinanciado = round($total - $initialAmount, 2);
            $montoBase = round($saldoFinanciado / $installmentsCount, 2);
            $acumulado = 0;

            for ($n = 1; $n <= $installmentsCount; $n++) {
                $monto = $n < $installmentsCount ? $montoBase : round($saldoFinanciado - $acumulado, 2);
                $acumulado += $monto;

                $dueDate = Carbon::parse($date)->addMonthsNoOverflow($n);

                if ($isAnulada) {
                    // una venta anulada anula también sus cuotas, sin importar la fecha
                    $status = 'Anulada';
                    $paidAt = null;
                } elseif ($dueDate->isPast()) {
                    // cuota ya vencida: la mitad se pagaron a tiempo, la otra mitad quedó vencida
                    $status = $n % 2 === 0 ? 'Pagada' : 'Pendiente';
                    $paidAt = $status === 'Pagada' ? $dueDate : null;
                } else {
                    $status = 'Pendiente';
                    $paidAt = null;
                }

                $installment = Installment::create([
                    'number' => $n,
                    'due_date' => $dueDate,
                    'amount' => $monto,
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'sale_id' => $sale->id,
                ]);

                if ($status === 'Pagada') {
                    Payment::create([
                        'amount' => $monto,
                        'payment_method' => $faker->randomElement($paymentMethods),
                        'payment_status' => 'Cuota',
                        'sale_id' => $sale->id,
                        'installment_id' => $installment->id,
                    ]);
                }
            }
        }
    }
}
