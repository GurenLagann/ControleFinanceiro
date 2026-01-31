<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Receita;
use App\Models\Despesa;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinancaControllerTest extends TestCase
{
    /**
     * Testa se a pagina inicial carrega corretamente
     */
    public function test_pagina_inicial_carrega_com_sucesso(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('financas.index');
    }

    /**
     * Testa se a pagina de transacoes carrega corretamente
     */
    public function test_pagina_transacoes_carrega_com_sucesso(): void
    {
        $response = $this->get('/transacoes');

        $response->assertStatus(200);
        $response->assertViewIs('financas.transacoes');
    }

    /**
     * Testa criacao de receita
     */
    public function test_criar_receita_com_sucesso(): void
    {
        $dados = [
            'descricao' => 'Salario Teste',
            'valor' => 5000.00,
            'data' => now()->format('Y-m-d'),
            'categoria' => 'Salario',
        ];

        $response = $this->post('/receitas', $dados);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    /**
     * Testa validacao de receita sem descricao
     */
    public function test_criar_receita_sem_descricao_falha(): void
    {
        $dados = [
            'valor' => 5000.00,
            'data' => now()->format('Y-m-d'),
        ];

        $response = $this->post('/receitas', $dados);

        $response->assertSessionHasErrors('descricao');
    }

    /**
     * Testa criacao de despesa simples
     */
    public function test_criar_despesa_simples_com_sucesso(): void
    {
        $dados = [
            'descricao' => 'Aluguel Teste',
            'valor' => 1500.00,
            'data' => now()->format('Y-m-d'),
            'categoria' => 'Moradia',
        ];

        $response = $this->post('/despesas', $dados);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    /**
     * Testa criacao de despesa parcelada
     */
    public function test_criar_despesa_parcelada_com_sucesso(): void
    {
        $dados = [
            'descricao' => 'TV Nova',
            'valor' => 3000.00,
            'data' => now()->format('Y-m-d'),
            'categoria' => 'Eletronicos',
            'parcelado' => '1',
            'total_parcelas' => 10,
        ];

        $response = $this->post('/despesas', $dados);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    /**
     * Testa validacao de despesa sem valor
     */
    public function test_criar_despesa_sem_valor_falha(): void
    {
        $dados = [
            'descricao' => 'Teste',
            'data' => now()->format('Y-m-d'),
        ];

        $response = $this->post('/despesas', $dados);

        $response->assertSessionHasErrors('valor');
    }

    /**
     * Testa multiplas despesas
     */
    public function test_criar_multiplas_despesas_com_sucesso(): void
    {
        $dados = [
            'data' => now()->format('Y-m-d'),
            'despesas' => [
                ['descricao' => 'Mercado', 'valor' => 200.00, 'categoria' => 'Alimentacao'],
                ['descricao' => 'Farmacia', 'valor' => 50.00, 'categoria' => 'Saude'],
            ],
        ];

        $response = $this->post('/despesas/multiplas', $dados);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    /**
     * Testa que a view principal contem as variaveis necessarias
     */
    public function test_view_principal_contem_variaveis(): void
    {
        $response = $this->get('/');

        $response->assertViewHasAll([
            'receitas',
            'despesas',
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'totalDespesasMesAtual',
            'totalReceitasMesAtual',
            'saldoMesAtual',
        ]);
    }
}
