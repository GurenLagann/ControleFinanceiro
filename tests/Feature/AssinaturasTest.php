<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Despesa;
use App\Models\Receita;
use Tests\TestCase;

class AssinaturasTest extends TestCase
{
    /**
     * Testa se o dashboard expoe o total mensal de recorrentes e o percentual da renda
     */
    public function test_dashboard_expoe_total_e_percentual_de_assinaturas(): void
    {
        $receita = Receita::create([
            'descricao' => 'Salario Assinaturas Teste',
            'valor' => 1000,
            'data' => now(),
            'recorrente' => false,
            'ativo' => true,
        ]);

        $recorrente = Despesa::create([
            'descricao' => 'Streaming Assinaturas Teste',
            'valor' => 400,
            'data' => now(),
            'recorrente' => true,
            'frequencia' => 'mensal',
            'dia_vencimento' => now()->day,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/');

        $response->assertViewHas('totalRecorrentesMensal', function ($total) {
            return $total >= 400.0;
        });
        $response->assertViewHas('percentualRecorrentesRenda');

        $recorrente->delete();
        $receita->delete();
    }

    /**
     * Testa se um alerta e gerado quando as assinaturas comprometem 30% ou mais da renda
     */
    public function test_alerta_gerado_quando_assinaturas_comprometem_a_renda(): void
    {
        $receita = Receita::create([
            'descricao' => 'Salario Comprometido Teste',
            'valor' => 100,
            'data' => now(),
            'recorrente' => false,
            'ativo' => true,
        ]);

        $recorrente = Despesa::create([
            'descricao' => 'Assinatura Cara Teste',
            'valor' => 80,
            'data' => now(),
            'recorrente' => true,
            'frequencia' => 'mensal',
            'dia_vencimento' => now()->day,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $this->get('/alertas');

        $alerta = Alerta::where('tipo', 'info')
            ->where('referencia_tipo', 'assinaturas')
            ->first();

        $this->assertNotNull($alerta, 'Esperava um alerta informativo sobre assinaturas comprometendo a renda.');

        $alerta?->delete();
        $recorrente->delete();
        $receita->delete();
    }
}
