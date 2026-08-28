<?php

namespace Tests\Feature;

use App\Models\Despesa;
use Carbon\Carbon;
use Tests\TestCase;

class InsightsTest extends TestCase
{
    /**
     * Testa se o dashboard expoe insights automaticos quando ha variacao relevante de gastos
     */
    public function test_dashboard_expoe_insight_de_aumento_de_gasto(): void
    {
        // Mes anterior: R$ 100 em Lazer
        $anterior = Despesa::create([
            'descricao' => 'Lazer Mes Anterior Teste',
            'valor' => 100,
            'data' => Carbon::now()->subMonth(),
            'categoria' => 'Lazer',
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        // Mes atual: R$ 200 em Lazer (aumento de 100%)
        $atual = Despesa::create([
            'descricao' => 'Lazer Mes Atual Teste',
            'valor' => 200,
            'data' => Carbon::now(),
            'categoria' => 'Lazer',
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/');

        $response->assertViewHas('insights', function ($insights) {
            return collect($insights)->contains(fn ($texto) => str_contains($texto, 'Lazer'));
        });

        $anterior->delete();
        $atual->delete();
    }
}
