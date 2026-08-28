<?php

namespace Tests\Unit;

use App\Services\QuitacaoDividasService;
use PHPUnit\Framework\TestCase;

class QuitacaoDividasServiceTest extends TestCase
{
    public function test_lista_vazia_retorna_vazio(): void
    {
        $ordem = QuitacaoDividasService::ordenar([], 'snowball');

        $this->assertSame([], $ordem);
    }

    public function test_snowball_ordena_por_menor_valor_restante_primeiro(): void
    {
        $dividas = [
            ['id' => 'a', 'valor_restante' => 500, 'taxa_juros_mensal' => 1.0],
            ['id' => 'b', 'valor_restante' => 100, 'taxa_juros_mensal' => 3.0],
            ['id' => 'c', 'valor_restante' => 300, 'taxa_juros_mensal' => 2.0],
        ];

        $ordem = QuitacaoDividasService::ordenar($dividas, 'snowball');

        $this->assertSame(['b', 'c', 'a'], array_column($ordem, 'id'));
    }

    public function test_avalanche_ordena_por_maior_taxa_de_juros_primeiro(): void
    {
        $dividas = [
            ['id' => 'a', 'valor_restante' => 500, 'taxa_juros_mensal' => 1.5],
            ['id' => 'b', 'valor_restante' => 100, 'taxa_juros_mensal' => 5.0],
            ['id' => 'c', 'valor_restante' => 300, 'taxa_juros_mensal' => 2.0],
        ];

        $ordem = QuitacaoDividasService::ordenar($dividas, 'avalanche');

        $this->assertSame(['b', 'c', 'a'], array_column($ordem, 'id'));
    }

    public function test_avalanche_trata_taxa_nula_como_zero_e_deixa_por_ultimo(): void
    {
        $dividas = [
            ['id' => 'a', 'valor_restante' => 500, 'taxa_juros_mensal' => null],
            ['id' => 'b', 'valor_restante' => 100, 'taxa_juros_mensal' => 5.0],
        ];

        $ordem = QuitacaoDividasService::ordenar($dividas, 'avalanche');

        $this->assertSame(['b', 'a'], array_column($ordem, 'id'));
    }

    public function test_avalanche_desempata_taxa_igual_por_menor_valor_restante(): void
    {
        $dividas = [
            ['id' => 'a', 'valor_restante' => 500, 'taxa_juros_mensal' => 2.0],
            ['id' => 'b', 'valor_restante' => 100, 'taxa_juros_mensal' => 2.0],
        ];

        $ordem = QuitacaoDividasService::ordenar($dividas, 'avalanche');

        $this->assertSame(['b', 'a'], array_column($ordem, 'id'));
    }
}
