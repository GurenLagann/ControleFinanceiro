<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Despesa;
use App\Models\Meta;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class GamificacaoTest extends TestCase
{
    /**
     * Testa se o dashboard expoe o streak de dias com lancamentos
     */
    public function test_dashboard_expoe_streak_dias(): void
    {
        $receita = Receita::create([
            'descricao' => 'Streak Teste',
            'valor' => 10,
            'data' => now(),
            'recorrente' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/');

        $response->assertViewHas('streakDias', function ($streakDias) {
            return $streakDias >= 1;
        });

        $receita->delete();
    }

    /**
     * Testa se uma conquista e criada quando uma meta atinge 100%
     */
    public function test_conquista_criada_quando_meta_atinge_100_por_cento(): void
    {
        $meta = Meta::create([
            'titulo' => 'Meta Conquista Teste',
            'valor_alvo' => 100,
            'valor_atual' => 100,
            'data_inicio' => now()->subMonth(),
            'data_fim' => now()->addMonth(),
            'tipo' => 'economia',
            'ativo' => true,
        ]);

        $this->get('/alertas');

        $conquista = Alerta::where('tipo', 'conquista')
            ->where('referencia_tipo', 'meta')
            ->where('referencia_id', $meta->_id)
            ->first();

        $this->assertNotNull($conquista, 'Esperava um alerta de conquista para a meta concluida.');

        $conquista?->delete();
        $meta->delete();
    }

    /**
     * Testa se gerar alertas duas vezes nao duplica a conquista da meta
     */
    public function test_conquista_de_meta_nao_e_duplicada(): void
    {
        $meta = Meta::create([
            'titulo' => 'Meta Conquista Duplicada Teste',
            'valor_alvo' => 50,
            'valor_atual' => 50,
            'data_inicio' => now()->subMonth(),
            'data_fim' => now()->addMonth(),
            'tipo' => 'economia',
            'ativo' => true,
        ]);

        $this->get('/alertas');
        $this->get('/alertas');

        $count = Alerta::where('tipo', 'conquista')
            ->where('referencia_tipo', 'meta')
            ->where('referencia_id', $meta->_id)
            ->count();

        $this->assertSame(1, $count);

        Alerta::where('tipo', 'conquista')->where('referencia_id', $meta->_id)->delete();
        $meta->delete();
    }

    /**
     * Testa se uma conquista e criada quando todas as parcelas de um grupo sao quitadas
     */
    public function test_conquista_criada_quando_grupo_de_parcelas_e_quitado(): void
    {
        $grupoId = (string) Str::uuid();

        for ($i = 1; $i <= 2; $i++) {
            Despesa::create([
                'descricao' => 'Parcela Conquista Teste',
                'valor' => 50,
                'valor_total' => 100,
                'data' => now()->subDays($i),
                'parcelado' => true,
                'parcela_atual' => $i,
                'total_parcelas' => 2,
                'grupo_parcela_id' => $grupoId,
                'recorrente' => false,
                'ativo' => true,
            ]);
        }

        $this->get('/alertas');

        $conquista = Alerta::where('tipo', 'conquista')
            ->where('referencia_tipo', 'despesa_grupo')
            ->where('referencia_id', $grupoId)
            ->first();

        $this->assertNotNull($conquista, 'Esperava um alerta de conquista para o grupo de parcelas quitado.');

        $conquista?->delete();
        Despesa::where('grupo_parcela_id', $grupoId)->delete();
    }

    /**
     * Testa que um grupo de parcelas ainda com parcela futura nao gera conquista
     */
    public function test_grupo_de_parcelas_nao_quitado_nao_gera_conquista(): void
    {
        $grupoId = (string) Str::uuid();

        Despesa::create([
            'descricao' => 'Parcela Paga Teste',
            'valor' => 50,
            'valor_total' => 100,
            'data' => now()->subDay(),
            'parcelado' => true,
            'parcela_atual' => 1,
            'total_parcelas' => 2,
            'grupo_parcela_id' => $grupoId,
            'recorrente' => false,
            'ativo' => true,
        ]);

        Despesa::create([
            'descricao' => 'Parcela Futura Teste',
            'valor' => 50,
            'valor_total' => 100,
            'data' => now()->addMonth(),
            'parcelado' => true,
            'parcela_atual' => 2,
            'total_parcelas' => 2,
            'grupo_parcela_id' => $grupoId,
            'recorrente' => false,
            'ativo' => true,
        ]);

        $this->get('/alertas');

        $conquista = Alerta::where('tipo', 'conquista')
            ->where('referencia_tipo', 'despesa_grupo')
            ->where('referencia_id', $grupoId)
            ->first();

        $this->assertNull($conquista);

        Despesa::where('grupo_parcela_id', $grupoId)->delete();
    }
}
