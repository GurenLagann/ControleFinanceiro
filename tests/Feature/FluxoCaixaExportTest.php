<?php

namespace Tests\Feature;

use App\Models\Despesa;
use App\Models\Receita;
use Tests\TestCase;

class FluxoCaixaExportTest extends TestCase
{
    public function test_exportar_csv_do_fluxo_de_caixa_de_um_ano(): void
    {
        Receita::create(['descricao' => 'Salario', 'valor' => 5000, 'data' => '2026-03-05']);
        Despesa::create(['descricao' => 'Aluguel', 'valor' => 1500, 'data' => '2026-03-10']);

        $response = $this->get('/exportar/csv/fluxo-caixa?ano=2026');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertSee('Mar/26', false);
        $response->assertSee('5.000,00', false);
        $response->assertSee('1.500,00', false);
    }

    public function test_exportar_pdf_do_fluxo_de_caixa_de_um_ano(): void
    {
        Receita::create(['descricao' => 'Salario', 'valor' => 5000, 'data' => '2026-03-05']);

        $response = $this->get('/exportar/pdf/fluxo-caixa?ano=2026');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
