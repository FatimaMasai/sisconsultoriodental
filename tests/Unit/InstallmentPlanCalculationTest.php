<?php

namespace Tests\Unit;

use App\Models\Installment;
use PHPUnit\Framework\TestCase;

/**
 * Cubre Installment::planAmounts(), el cálculo que arma el plan de cuotas
 * de una venta a Crédito (usado por SaleController::store()). Es el punto
 * de más riesgo de bugs silenciosos con plata real: si las cuotas no
 * suman exactamente el saldo financiado, el paciente termina debiendo (o
 * la clínica cobrando) centavos de más o de menos sin que nadie lo note.
 *
 * Estos tests no tocan la base de datos ni requieren que Laravel esté
 * arrancado (extienden PHPUnit\Framework\TestCase, no Tests\TestCase),
 * así que corren rápido y no dependen de que MySQL/SQLite estén
 * configurados en este equipo.
 */
class InstallmentPlanCalculationTest extends TestCase
{
    public function test_se_reparte_en_partes_iguales_cuando_divide_exacto(): void
    {
        $montos = Installment::planAmounts(900.00, 3);

        $this->assertSame([300.0, 300.0, 300.0], $montos);
        $this->assertSame(900.0, round(array_sum($montos), 2));
    }

    public function test_la_ultima_cuota_absorbe_el_ajuste_de_redondeo(): void
    {
        // 1000 / 3 = 333.333... -> cada cuota individual redondeada da 333.33,
        // pero 3 x 333.33 = 999.99, NO 1000. La última cuota debe compensar
        // ese centavo faltante para que la suma cuadre exacto.
        $montos = Installment::planAmounts(1000.00, 3);

        $this->assertCount(3, $montos);
        $this->assertSame(333.33, $montos[0]);
        $this->assertSame(333.33, $montos[1]);
        $this->assertSame(333.34, $montos[2]);
        $this->assertSame(1000.0, round(array_sum($montos), 2));
    }

    public function test_una_sola_cuota_se_lleva_el_saldo_completo(): void
    {
        $montos = Installment::planAmounts(500.00, 1);

        $this->assertSame([500.0], $montos);
    }

    public function test_funciona_con_montos_que_no_son_multiplos_de_centavos_exactos(): void
    {
        // Caso real: cuota inicial deja un saldo con decimales feos.
        $montos = Installment::planAmounts(1234.56, 7);

        $this->assertCount(7, $montos);
        // Lo que más importa: la suma de las cuotas SIEMPRE debe cuadrar
        // exacto con el saldo financiado, centavo a centavo.
        $this->assertSame(1234.56, round(array_sum($montos), 2));
    }

    public function test_muchas_cuotas_igual_cuadran_exacto(): void
    {
        // Caso límite: 36 cuotas (el máximo que permite el formulario de venta).
        $montos = Installment::planAmounts(10000.00, 36);

        $this->assertCount(36, $montos);
        $this->assertSame(10000.0, round(array_sum($montos), 2));
    }

    public function test_rechaza_un_numero_de_cuotas_invalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Installment::planAmounts(1000.00, 0);
    }
}
