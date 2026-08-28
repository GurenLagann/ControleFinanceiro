<?php

namespace Tests\Unit;

use App\Services\GamificacaoService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class GamificacaoServiceTest extends TestCase
{
    public function test_sem_lancamentos_streak_e_zero(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak([], $hoje);

        $this->assertSame(0, $streak);
    }

    public function test_lancamento_hoje_streak_e_um(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak(['2026-08-27'], $hoje);

        $this->assertSame(1, $streak);
    }

    public function test_tres_dias_seguidos_ate_hoje_streak_e_tres(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak(
            ['2026-08-27', '2026-08-26', '2026-08-25'],
            $hoje
        );

        $this->assertSame(3, $streak);
    }

    public function test_ultimo_lancamento_foi_ontem_streak_continua_por_tolerancia(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak(
            ['2026-08-26', '2026-08-25'],
            $hoje
        );

        $this->assertSame(2, $streak);
    }

    public function test_ultimo_lancamento_foi_ha_tres_dias_streak_quebrou(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak(['2026-08-24'], $hoje);

        $this->assertSame(0, $streak);
    }

    public function test_gap_no_meio_conta_so_os_dias_seguidos_mais_recentes(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        // hoje e ontem lancados, mas falhou 25/08, depois tem 24/08 - nao deve contar o 24
        $streak = GamificacaoService::calcularStreak(
            ['2026-08-27', '2026-08-26', '2026-08-24'],
            $hoje
        );

        $this->assertSame(2, $streak);
    }

    public function test_datas_duplicadas_no_mesmo_dia_contam_uma_vez(): void
    {
        $hoje = Carbon::parse('2026-08-27');

        $streak = GamificacaoService::calcularStreak(
            ['2026-08-27', '2026-08-27', '2026-08-26'],
            $hoje
        );

        $this->assertSame(2, $streak);
    }
}
