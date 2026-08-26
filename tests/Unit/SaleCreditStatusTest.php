<?php

namespace Tests\Unit;

use App\Models\Installment;
use App\Models\Sale;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Cubre Sale::saldo_pendiente y Sale::estado_credito (los accessors que
 * le dicen a recepción, de un vistazo, cuánto debe un paciente y si va
 * al día o tiene cuotas vencidas). Es la lógica de negocio más sensible
 * del sistema de créditos: un error acá significa cobrar de más/menos o
 * mostrar como "al día" a alguien que en realidad debe.
 *
 * No requiere base de datos: se arma la venta y sus cuotas en memoria con
 * setRelation(), sin guardar nada. Eloquent resuelve $sale->installments
 * desde esa relación ya cargada en vez de consultar la BD.
 */
class SaleCreditStatusTest extends TestCase
{
    private function ventaCredito(float $total, float $initialAmount, array $cuotas): Sale
    {
        $sale = new Sale([
            'payment_type' => 'Credito',
            'total' => $total,
            'initial_amount' => $initialAmount,
            'status' => 1,
        ]);

        $sale->setRelation('installments', collect($cuotas));

        return $sale;
    }

    public function test_una_venta_al_contado_no_tiene_saldo_ni_estado_de_credito(): void
    {
        $sale = new Sale([
            'payment_type' => 'Contado',
            'total' => 500,
            'initial_amount' => 0,
            'status' => 1,
        ]);

        $this->assertSame(0.0, $sale->saldo_pendiente);
        $this->assertNull($sale->estado_credito);
    }

    public function test_credito_sin_cuotas_pagadas_todavia_esta_al_dia(): void
    {
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Pendiente', 'amount' => 400, 'due_date' => Carbon::tomorrow()]),
            new Installment(['status' => 'Pendiente', 'amount' => 400, 'due_date' => Carbon::tomorrow()->addMonth()]),
        ]);

        $this->assertSame(800.0, $sale->saldo_pendiente); // 1000 - 200 inicial - 0 pagado
        $this->assertSame('Al día', $sale->estado_credito);
    }

    public function test_credito_con_una_cuota_vencida_sin_pagar_queda_pendiente(): void
    {
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Pendiente', 'amount' => 400, 'due_date' => Carbon::yesterday()]),
            new Installment(['status' => 'Pendiente', 'amount' => 400, 'due_date' => Carbon::tomorrow()]),
        ]);

        $this->assertSame('Pendiente', $sale->estado_credito);
    }

    public function test_credito_con_todas_las_cuotas_pagadas_queda_completado(): void
    {
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Pagada', 'amount' => 400, 'due_date' => Carbon::yesterday()]),
            new Installment(['status' => 'Pagada', 'amount' => 400, 'due_date' => Carbon::yesterday()]),
        ]);

        $this->assertSame(0.0, $sale->saldo_pendiente); // 1000 - 200 - 800 pagado
        $this->assertSame('Completado', $sale->estado_credito);
    }

    public function test_pago_parcial_de_cuotas_descuenta_del_saldo_pendiente(): void
    {
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Pagada', 'amount' => 300, 'due_date' => Carbon::yesterday()]),
            new Installment(['status' => 'Pendiente', 'amount' => 500, 'due_date' => Carbon::tomorrow()]),
        ]);

        // 1000 total - 200 cuota inicial - 300 ya pagado en cuotas = 500 pendiente
        $this->assertSame(500.0, $sale->saldo_pendiente);
        $this->assertSame('Al día', $sale->estado_credito); // la única pendiente no está vencida
    }

    public function test_credito_con_todas_las_cuotas_anuladas_queda_anulado(): void
    {
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Anulada', 'amount' => 400, 'due_date' => Carbon::yesterday()]),
            new Installment(['status' => 'Anulada', 'amount' => 400, 'due_date' => Carbon::tomorrow()]),
        ]);

        $this->assertSame('Anulado', $sale->estado_credito);
    }

    public function test_una_cuota_anulada_no_cuenta_para_saber_si_esta_vencida(): void
    {
        // Cuota vencida pero anulada + una cuota activa al día -> no debería
        // marcar la venta como "Pendiente" por una cuota que ya no aplica.
        $sale = $this->ventaCredito(1000, 200, [
            new Installment(['status' => 'Anulada', 'amount' => 400, 'due_date' => Carbon::yesterday()]),
            new Installment(['status' => 'Pendiente', 'amount' => 400, 'due_date' => Carbon::tomorrow()]),
        ]);

        $this->assertSame('Al día', $sale->estado_credito);
    }
}
