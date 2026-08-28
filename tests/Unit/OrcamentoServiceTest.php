<?php

namespace Tests\Unit;

use App\Services\OrcamentoService;
use PHPUnit\Framework\TestCase;

class OrcamentoServiceTest extends TestCase
{
    public function test_gasto_zero_fica_ok(): void
    {
        $progresso = OrcamentoService::calcularProgresso(1000, 0);

        $this->assertSame(0.0, $progresso['percentual']);
        $this->assertSame('ok', $progresso['status']);
    }

    public function test_gasto_abaixo_de_80_por_cento_fica_ok(): void
    {
        $progresso = OrcamentoService::calcularProgresso(1000, 500);

        $this->assertSame(50.0, $progresso['percentual']);
        $this->assertSame('ok', $progresso['status']);
    }

    public function test_gasto_entre_80_e_100_por_cento_fica_atencao(): void
    {
        $progresso = OrcamentoService::calcularProgresso(1000, 850);

        $this->assertSame(85.0, $progresso['percentual']);
        $this->assertSame('atencao', $progresso['status']);
    }

    public function test_gasto_igual_ao_orcamento_fica_excedido(): void
    {
        $progresso = OrcamentoService::calcularProgresso(1000, 1000);

        $this->assertSame(100.0, $progresso['percentual']);
        $this->assertSame('excedido', $progresso['status']);
    }

    public function test_gasto_acima_do_orcamento_mantem_percentual_real(): void
    {
        $progresso = OrcamentoService::calcularProgresso(1000, 1500);

        $this->assertSame(150.0, $progresso['percentual']);
        $this->assertSame('excedido', $progresso['status']);
    }

    public function test_orcamento_zero_ou_negativo_nao_gera_divisao_por_zero(): void
    {
        $progresso = OrcamentoService::calcularProgresso(0, 100);

        $this->assertSame(0.0, $progresso['percentual']);
        $this->assertSame('ok', $progresso['status']);
    }
}
