<?php

namespace Tests\Unit;

use App\Services\InsightsService;
use PHPUnit\Framework\TestCase;

class InsightsServiceTest extends TestCase
{
    public function test_sem_gasto_atual_nem_anterior_nao_gera_insight(): void
    {
        $insight = InsightsService::gerarInsight('Lazer', 0.0, 0.0);

        $this->assertNull($insight);
    }

    public function test_variacao_pequena_nao_gera_insight(): void
    {
        $insight = InsightsService::gerarInsight('Lazer', 105.0, 100.0);

        $this->assertNull($insight);
    }

    public function test_aumento_significativo_gera_insight_de_alta(): void
    {
        $insight = InsightsService::gerarInsight('Lazer', 115.0, 100.0);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('15%', $insight['texto']);
        $this->assertStringContainsString('a mais', $insight['texto']);
        $this->assertStringContainsString('Lazer', $insight['texto']);
        $this->assertSame(15.0, $insight['relevancia']);
    }

    public function test_reducao_significativa_gera_insight_de_baixa(): void
    {
        $insight = InsightsService::gerarInsight('Transporte', 85.0, 100.0);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('15%', $insight['texto']);
        $this->assertStringContainsString('a menos', $insight['texto']);
    }

    public function test_gasto_novo_sem_historico_anterior_gera_insight(): void
    {
        $insight = InsightsService::gerarInsight('Viagem', 200.0, 0.0);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Viagem', $insight['texto']);
    }

    public function test_ordenar_prioriza_maior_relevancia(): void
    {
        $insights = [
            ['texto' => 'baixa relevancia', 'relevancia' => 15.0],
            ['texto' => 'alta relevancia', 'relevancia' => 50.0],
        ];

        $ordenados = InsightsService::ordenarPorRelevancia($insights);

        $this->assertSame('alta relevancia', $ordenados[0]['texto']);
    }
}
