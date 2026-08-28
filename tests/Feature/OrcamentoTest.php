<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Categoria;
use App\Models\Despesa;
use Tests\TestCase;

class OrcamentoTest extends TestCase
{
    /**
     * Testa se e possivel salvar um orcamento mensal na categoria
     */
    public function test_categoria_pode_ter_orcamento_mensal(): void
    {
        $dados = [
            'nome' => 'Categoria Orcamento Teste',
            'cor' => '#00ff88',
            'tipo' => 'despesa',
            'orcamento_mensal' => 500,
        ];

        $response = $this->post('/categorias', $dados);

        $response->assertRedirect();
        $categoria = Categoria::where('nome', 'Categoria Orcamento Teste')->first();
        $this->assertNotNull($categoria);
        $this->assertSame(500.0, (float) $categoria->orcamento_mensal);

        $categoria->delete();
    }

    /**
     * Testa se o dashboard expoe o progresso de orcamento por categoria
     */
    public function test_dashboard_expoe_progresso_de_orcamento(): void
    {
        $categoria = Categoria::create([
            'nome' => 'Alimentacao Orcamento Teste',
            'cor' => '#ff4757',
            'tipo' => 'despesa',
            'ativo' => true,
            'orcamento_mensal' => 100,
        ]);

        $despesa = Despesa::create([
            'descricao' => 'Mercado Orcamento Teste',
            'valor' => 60,
            'data' => now(),
            'categoria' => $categoria->nome,
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/');

        $response->assertViewHas('orcamentos', function ($orcamentos) use ($categoria) {
            $item = collect($orcamentos)->firstWhere('categoria', $categoria->nome);

            return $item
                && $item['gasto'] === 60.0
                && $item['orcamento'] === 100.0
                && $item['percentual'] === 60.0
                && $item['status'] === 'ok';
        });

        $despesa->delete();
        $categoria->delete();
    }

    /**
     * Testa se categoria sem orcamento_mensal nao aparece na lista de orcamentos
     */
    public function test_categoria_sem_orcamento_nao_aparece_no_progresso(): void
    {
        $categoria = Categoria::create([
            'nome' => 'Categoria Sem Orcamento Teste',
            'cor' => '#3742fa',
            'tipo' => 'despesa',
            'ativo' => true,
        ]);

        $response = $this->get('/');

        $response->assertViewHas('orcamentos', function ($orcamentos) use ($categoria) {
            return collect($orcamentos)->firstWhere('categoria', $categoria->nome) === null;
        });

        $categoria->delete();
    }

    /**
     * Testa se um alerta de limite e criado quando o orcamento de uma categoria e ultrapassado
     */
    public function test_alerta_de_limite_criado_quando_orcamento_de_categoria_e_ultrapassado(): void
    {
        $categoria = Categoria::create([
            'nome' => 'Lazer Orcamento Teste',
            'cor' => '#f59e0b',
            'tipo' => 'despesa',
            'ativo' => true,
            'orcamento_mensal' => 100,
        ]);

        $despesa = Despesa::create([
            'descricao' => 'Cinema Orcamento Teste',
            'valor' => 150,
            'data' => now(),
            'categoria' => $categoria->nome,
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $this->get('/alertas');

        $alerta = Alerta::where('tipo', 'limite')
            ->where('referencia_tipo', 'categoria')
            ->where('referencia_id', $categoria->_id)
            ->first();

        $this->assertNotNull($alerta, 'Esperava um alerta de limite para a categoria com orcamento ultrapassado.');

        $alerta?->delete();
        $despesa->delete();
        $categoria->delete();
    }

    /**
     * Testa que gerar alertas duas vezes nao duplica o alerta de limite da categoria
     */
    public function test_alerta_de_limite_de_categoria_nao_e_duplicado(): void
    {
        $categoria = Categoria::create([
            'nome' => 'Transporte Orcamento Teste',
            'cor' => '#6f42c1',
            'tipo' => 'despesa',
            'ativo' => true,
            'orcamento_mensal' => 50,
        ]);

        $despesa = Despesa::create([
            'descricao' => 'Uber Orcamento Teste',
            'valor' => 80,
            'data' => now(),
            'categoria' => $categoria->nome,
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $this->get('/alertas');
        $this->get('/alertas');

        $count = Alerta::where('tipo', 'limite')
            ->where('referencia_tipo', 'categoria')
            ->where('referencia_id', $categoria->_id)
            ->count();

        $this->assertSame(1, $count);

        Alerta::where('referencia_tipo', 'categoria')->where('referencia_id', $categoria->_id)->delete();
        $despesa->delete();
        $categoria->delete();
    }
}
