<?php

namespace Tests\Unit;

use App\Models\Installment;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Cubre Installment::estado_actual (getEstadoActualAttribute), que decide
 * si una cuota está Pendiente, Vencida, Pagada o Anulada sin depender de
 * un job programado que actualice la BD — se calcula "al vuelo" comparando
 * con la fecha actual. Si este cálculo se rompe, una cuota vencida puede
 * mostrarse como "al día" y nadie se entera de que el paciente debe.
 *
 * No requiere base de datos: se construyen instancias de Installment en
 * memoria con new Installment([...]), sin guardarlas.
 */
class InstallmentEstadoActualTest extends TestCase
{
    public function test_una_cuota_pagada_se_muestra_pagada_sin_importar_la_fecha(): void
    {
        $cuota = new Installment([
            'status' => 'Pagada',
            'due_date' => Carbon::yesterday(), // vencida por fecha, pero ya pagada
        ]);

        $this->assertSame('Pagada', $cuota->estado_actual);
    }

    public function test_una_cuota_anulada_se_muestra_anulada_sin_importar_la_fecha(): void
    {
        $cuota = new Installment([
            'status' => 'Anulada',
            'due_date' => Carbon::yesterday(),
        ]);

        $this->assertSame('Anulada', $cuota->estado_actual);
    }

    public function test_una_cuota_pendiente_con_fecha_pasada_esta_vencida(): void
    {
        $cuota = new Installment([
            'status' => 'Pendiente',
            'due_date' => Carbon::yesterday(),
        ]);

        $this->assertSame('Vencida', $cuota->estado_actual);
    }

    public function test_una_cuota_pendiente_con_fecha_futura_sigue_pendiente(): void
    {
        $cuota = new Installment([
            'status' => 'Pendiente',
            'due_date' => Carbon::tomorrow(),
        ]);

        $this->assertSame('Pendiente', $cuota->estado_actual);
    }

    public function test_una_cuota_que_vence_hoy_ya_cuenta_como_vencida(): void
    {
        // 'due_date' castea a 'date' (medianoche de hoy), así que
        // isPast() da true apenas pasa un instante del día -> el sistema
        // ya la trata como vencida el mismo día que vence, no al día
        // siguiente. Documentamos este comportamiento real con un test
        // para que un cambio futuro en el cast no lo cambie sin querer.
        $cuota = new Installment([
            'status' => 'Pendiente',
            'due_date' => Carbon::today(),
        ]);

        $this->assertSame('Vencida', $cuota->estado_actual);
    }
}
