<?php

namespace Tests\Unit;

use App\Services\AssinaturasService;
use PHPUnit\Framework\TestCase;

class AssinaturasServiceTest extends TestCase
{
    public function test_percentual_da_renda_calcula_corretamente(): void
    {
        $percentual = AssinaturasService::percentualDaRenda(300.0, 3000.0);

        $this->assertSame(10.0, $percentual);
    }

    public function test_renda_zero_nao_gera_divisao_por_zero(): void
    {
        $percentual = AssinaturasService::percentualDaRenda(300.0, 0.0);

        $this->assertSame(0.0, $percentual);
    }

    public function test_comprometida_e_verdadeiro_quando_percentual_maior_ou_igual_a_30(): void
    {
        $this->assertTrue(AssinaturasService::comprometida(30.0));
        $this->assertTrue(AssinaturasService::comprometida(45.0));
        $this->assertFalse(AssinaturasService::comprometida(29.9));
    }
}
