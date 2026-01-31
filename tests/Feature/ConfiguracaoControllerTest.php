<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Categoria;
use App\Models\Meta;
use App\Models\Alerta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfiguracaoControllerTest extends TestCase
{
    /**
     * Testa se a pagina de categorias carrega
     */
    public function test_pagina_categorias_carrega(): void
    {
        $response = $this->get('/categorias');

        $response->assertStatus(200);
        $response->assertViewIs('configuracoes.categorias');
    }

    /**
     * Testa criacao de categoria
     */
    public function test_criar_categoria_com_sucesso(): void
    {
        $dados = [
            'nome' => 'Categoria Teste',
            'cor' => '#ff0000',
            'icone' => 'cart',
            'tipo' => 'ambos',
        ];

        $response = $this->post('/categorias', $dados);

        $response->assertRedirect('/categorias');
        $response->assertSessionHas('success');
    }

    /**
     * Testa validacao de categoria sem nome
     */
    public function test_criar_categoria_sem_nome_falha(): void
    {
        $dados = [
            'cor' => '#ff0000',
            'tipo' => 'ambos',
        ];

        $response = $this->post('/categorias', $dados);

        $response->assertSessionHasErrors('nome');
    }

    /**
     * Testa se a pagina de metas carrega
     */
    public function test_pagina_metas_carrega(): void
    {
        $response = $this->get('/metas');

        $response->assertStatus(200);
        $response->assertViewIs('configuracoes.metas');
    }

    /**
     * Testa criacao de meta
     */
    public function test_criar_meta_com_sucesso(): void
    {
        $dados = [
            'titulo' => 'Meta Teste',
            'descricao' => 'Descricao da meta',
            'valor_alvo' => 1000.00,
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addMonth()->format('Y-m-d'),
            'tipo' => 'economia',
        ];

        $response = $this->post('/metas', $dados);

        $response->assertRedirect('/metas');
        $response->assertSessionHas('success');
    }

    /**
     * Testa validacao de meta com data fim anterior a data inicio
     */
    public function test_criar_meta_data_invalida_falha(): void
    {
        $dados = [
            'titulo' => 'Meta Teste',
            'valor_alvo' => 1000.00,
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->subMonth()->format('Y-m-d'),
            'tipo' => 'economia',
        ];

        $response = $this->post('/metas', $dados);

        $response->assertSessionHasErrors('data_fim');
    }

    /**
     * Testa se a pagina de alertas carrega
     */
    public function test_pagina_alertas_carrega(): void
    {
        $response = $this->get('/alertas');

        $response->assertStatus(200);
        $response->assertViewIs('configuracoes.alertas');
    }

    /**
     * Testa marcar todos alertas como lidos
     */
    public function test_marcar_todos_alertas_lidos(): void
    {
        $response = $this->post('/alertas/marcar-todos-lidos');

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Testa API de categorias
     */
    public function test_api_categorias_retorna_json(): void
    {
        $response = $this->get('/api/categorias');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Testa API de alertas
     */
    public function test_api_alertas_retorna_json(): void
    {
        $response = $this->get('/api/alertas');

        $response->assertStatus(200);
        $response->assertJsonStructure(['alertas', 'count']);
    }
}
