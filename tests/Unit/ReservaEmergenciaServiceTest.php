<?php

namespace Tests\Unit;

use App\Services\ReservaEmergenciaService;
use PHPUnit\Framework\TestCase;

class ReservaEmergenciaServiceTest extends TestCase
{
    public function test_faixa_ideal_e_de_3x_a_6x_o_gasto_mensal(): void
    {
        $faixa = ReservaEmergenciaService::calcularFaixa(1000.0);

        $this->assertSame(3000.0, $faixa['minimo']);
        $this->assertSame(6000.0, $faixa['ideal']);
    }

    public function test_gasto_mensal_zero_gera_faixa_zerada(): void
    {
        $faixa = ReservaEmergenciaService::calcularFaixa(0.0);

        $this->assertSame(0.0, $faixa['minimo']);
        $this->assertSame(0.0, $faixa['ideal']);
    }

    public function test_gasto_mensal_negativo_e_tratado_como_zero(): void
    {
        $faixa = ReservaEmergenciaService::calcularFaixa(-500.0);

        $this->assertSame(0.0, $faixa['minimo']);
        $this->assertSame(0.0, $faixa['ideal']);
    }
}
