<?php

namespace Tests\Feature;

use App\Models\Despesa;
use App\Models\Receita;
use Tests\TestCase;

class RelatorioFluxoCaixaTest extends TestCase
{
    public function test_pagina_de_relatorio_carrega_com_sucesso(): void
    {
        $response = $this->get('/relatorios/fluxo-caixa');

        $response->assertOk();
        $response->assertViewIs('relatorios.fluxo-caixa');
        $response->assertViewHasAll(['fluxoCaixa', 'anoFluxo', 'anosDisponiveis']);
        $response->assertViewHas('anoFluxo', (int) now()->format('Y'));
    }

    public function test_pagina_de_relatorio_aceita_selecionar_outro_ano(): void
    {
        Receita::create(['descricao' => 'Salario', 'valor' => 1000, 'data' => '2020-05-10']);
        Despesa::create(['descricao' => 'Aluguel', 'valor' => 400, 'data' => '2020-05-15']);

        $response = $this->get('/relatorios/fluxo-caixa?ano=2020');

        $response->assertViewHas('anoFluxo', 2020);
        $response->assertViewHas('fluxoCaixa', function ($fluxo) {
            return $fluxo['receitas'][4] === 1000.0 && $fluxo['despesas'][4] === 400.0;
        });
    }
}
