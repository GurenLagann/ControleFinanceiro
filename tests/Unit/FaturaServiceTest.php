<?php

namespace Tests\Unit;

use App\Services\FaturaService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class FaturaServiceTest extends TestCase
{
    public function test_referencia_antes_do_fechamento_fica_no_ciclo_atual(): void
    {
        $ciclo = FaturaService::calcularCiclo(10, Carbon::parse('2026-08-05'));

        $this->assertSame('2026-07-11', $ciclo['inicio']->toDateString());
        $this->assertSame('2026-08-10', $ciclo['fim']->toDateString());
    }

    public function test_referencia_no_dia_do_fechamento_fica_no_ciclo_atual(): void
    {
        $ciclo = FaturaService::calcularCiclo(10, Carbon::parse('2026-08-10'));

        $this->assertSame('2026-07-11', $ciclo['inicio']->toDateString());
        $this->assertSame('2026-08-10', $ciclo['fim']->toDateString());
    }

    public function test_referencia_depois_do_fechamento_fica_no_proximo_ciclo(): void
    {
        $ciclo = FaturaService::calcularCiclo(10, Carbon::parse('2026-08-15'));

        $this->assertSame('2026-08-11', $ciclo['inicio']->toDateString());
        $this->assertSame('2026-09-10', $ciclo['fim']->toDateString());
    }

    public function test_dia_de_fechamento_maior_que_dias_do_mes_e_ajustado(): void
    {
        // Fevereiro/2026 tem 28 dias (nao e ano bissexto)
        $ciclo = FaturaService::calcularCiclo(31, Carbon::parse('2026-02-15'));

        $this->assertSame('2026-02-01', $ciclo['inicio']->toDateString());
        $this->assertSame('2026-02-28', $ciclo['fim']->toDateString());
    }
}
