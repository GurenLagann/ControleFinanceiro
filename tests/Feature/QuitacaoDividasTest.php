<?php

namespace Tests\Feature;

use App\Models\Divida;
use Tests\TestCase;

class QuitacaoDividasTest extends TestCase
{
    /**
     * Testa se e possivel salvar a taxa de juros mensal de uma divida
     */
    public function test_divida_pode_ter_taxa_de_juros_mensal(): void
    {
        $dados = [
            'descricao' => 'Divida Juros Teste',
            'valor_total' => 1000,
            'data_inicio' => now()->format('Y-m-d'),
            'taxa_juros_mensal' => 4.5,
        ];

        $response = $this->post('/dividas', $dados);

        $response->assertRedirect();
        $divida = Divida::where('descricao', 'Divida Juros Teste')->first();
        $this->assertNotNull($divida);
        $this->assertSame(4.5, (float) $divida->taxa_juros_mensal);

        $divida->delete();
    }

    /**
     * Testa se a pagina de dividas expoe o plano de quitacao snowball e avalanche
     */
    public function test_pagina_dividas_expoe_plano_de_quitacao(): void
    {
        $caraJuros = Divida::create([
            'descricao' => 'Cartao Juros Alto Teste',
            'valor_total' => 500,
            'data_inicio' => now(),
            'taxa_juros_mensal' => 10,
            'status' => 'ativa',
            'pagamentos' => [],
        ]);

        $menorSaldo = Divida::create([
            'descricao' => 'Emprestimo Saldo Baixo Teste',
            'valor_total' => 100,
            'data_inicio' => now(),
            'taxa_juros_mensal' => 1,
            'status' => 'ativa',
            'pagamentos' => [],
        ]);

        $response = $this->get('/dividas');

        $response->assertViewHas('planoQuitacao', function ($plano) use ($caraJuros, $menorSaldo) {
            $snowballIds = array_column($plano['snowball'], 'id');
            $avalancheIds = array_column($plano['avalanche'], 'id');

            $menorSaldoId = (string) $menorSaldo->_id;
            $caraJurosId = (string) $caraJuros->_id;

            $posSnowballMenor = array_search($menorSaldoId, $snowballIds);
            $posSnowballCaro = array_search($caraJurosId, $snowballIds);
            $posAvalancheMenor = array_search($menorSaldoId, $avalancheIds);
            $posAvalancheCaro = array_search($caraJurosId, $avalancheIds);

            return $posSnowballMenor !== false && $posSnowballCaro !== false
                && $posAvalancheMenor !== false && $posAvalancheCaro !== false
                && $posSnowballMenor < $posSnowballCaro
                && $posAvalancheCaro < $posAvalancheMenor;
        });

        $caraJuros->delete();
        $menorSaldo->delete();
    }

    /**
     * Testa que dividas quitadas nao entram no plano de quitacao
     */
    public function test_divida_quitada_nao_entra_no_plano_de_quitacao(): void
    {
        $quitada = Divida::create([
            'descricao' => 'Divida Quitada Teste',
            'valor_total' => 200,
            'data_inicio' => now(),
            'status' => 'quitada',
            'pagamentos' => [['id' => 'x', 'valor' => 200, 'data' => now()->format('Y-m-d')]],
        ]);

        $response = $this->get('/dividas');

        $response->assertViewHas('planoQuitacao', function ($plano) use ($quitada) {
            $quitadaId = (string) $quitada->_id;

            return !in_array($quitadaId, array_column($plano['snowball'], 'id'));
        });

        $quitada->delete();
    }
}
