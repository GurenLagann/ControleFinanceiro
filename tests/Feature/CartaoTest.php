<?php

namespace Tests\Feature;

use App\Models\Cartao;
use App\Models\Despesa;
use Carbon\Carbon;
use Tests\TestCase;

class CartaoTest extends TestCase
{
    public function test_criar_cartao_com_sucesso(): void
    {
        $dados = [
            'nome' => 'Cartao Teste',
            'dia_fechamento' => 10,
            'dia_vencimento' => 17,
            'limite' => 2000,
        ];

        $response = $this->post('/cartoes', $dados);

        $response->assertRedirect();
        $cartao = Cartao::where('nome', 'Cartao Teste')->first();
        $this->assertNotNull($cartao);
        $this->assertSame(10, $cartao->dia_fechamento);
        $this->assertSame(17, $cartao->dia_vencimento);

        $cartao->delete();
    }

    public function test_despesa_pode_ser_vinculada_a_um_cartao(): void
    {
        $cartao = Cartao::create([
            'nome' => 'Cartao Vinculo Teste',
            'dia_fechamento' => 10,
            'dia_vencimento' => 17,
            'ativo' => true,
        ]);

        $dados = [
            'descricao' => 'Compra no Cartao Teste',
            'valor' => 150,
            'data' => now()->format('Y-m-d'),
            'cartao_id' => (string) $cartao->_id,
        ];

        $response = $this->post('/despesas', $dados);

        $response->assertRedirect('/');
        $despesa = Despesa::where('descricao', 'Compra no Cartao Teste')->first();
        $this->assertNotNull($despesa);
        $this->assertSame((string) $cartao->_id, $despesa->cartao_id);

        $despesa->delete();
        $cartao->delete();
    }

    public function test_pagina_de_cartoes_expoe_fatura_atual(): void
    {
        $cartao = Cartao::create([
            'nome' => 'Cartao Fatura Teste',
            'dia_fechamento' => 28,
            'dia_vencimento' => 5,
            'ativo' => true,
        ]);

        $despesa = Despesa::create([
            'descricao' => 'Compra Fatura Teste',
            'valor' => 200,
            'data' => Carbon::now(),
            'cartao_id' => (string) $cartao->_id,
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/cartoes');

        $response->assertViewHas('faturas', function ($faturas) use ($cartao) {
            $item = collect($faturas)->firstWhere('cartao.id', (string) $cartao->_id);

            return $item && $item['total'] === 200.0;
        });

        $despesa->delete();
        $cartao->delete();
    }
}
