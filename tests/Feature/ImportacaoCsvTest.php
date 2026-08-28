<?php

namespace Tests\Feature;

use App\Models\Despesa;
use App\Models\Receita;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportacaoCsvTest extends TestCase
{
    protected function csvValido(): string
    {
        return "Data;Tipo;Descricao;Valor;Categoria\n"
            . "28/08/2026;Receita;\"Salario Importado Teste\";3000,00;\"Salario\"\n"
            . "linha invalida sem campos suficientes\n"
            . "05/01/2026;Despesa;\"Mercado Importado Teste\";150,50;\"Alimentacao\"\n";
    }

    public function test_pagina_importar_carrega_com_sucesso(): void
    {
        $response = $this->get('/importar');

        $response->assertStatus(200);
        $response->assertViewIs('importacao.index');
    }

    public function test_preview_mostra_linhas_validas_e_conta_erros(): void
    {
        $arquivo = UploadedFile::fake()->createWithContent('extrato.csv', $this->csvValido());

        $response = $this->post('/importar/preview', ['arquivo' => $arquivo]);

        $response->assertViewIs('importacao.preview');
        $response->assertViewHas('linhas', fn ($linhas) => count($linhas) === 2);
        $response->assertViewHas('erros', 1);
    }

    public function test_confirmar_importacao_cria_receitas_e_despesas(): void
    {
        $arquivo = UploadedFile::fake()->createWithContent('extrato.csv', $this->csvValido());
        $this->post('/importar/preview', ['arquivo' => $arquivo]);

        $response = $this->post('/importar/confirmar');

        $response->assertRedirect('/');

        $receita = Receita::where('descricao', 'Salario Importado Teste')->first();
        $despesa = Despesa::where('descricao', 'Mercado Importado Teste')->first();

        $this->assertNotNull($receita);
        $this->assertSame(3000.0, (float) $receita->valor);
        $this->assertNotNull($despesa);
        $this->assertSame(150.50, (float) $despesa->valor);

        $receita->delete();
        $despesa->delete();
    }
}
