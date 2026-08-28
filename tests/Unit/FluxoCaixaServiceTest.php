<?php

namespace Tests\Unit;

use App\Services\FluxoCaixaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class FluxoCaixaServiceTest extends TestCase
{
    private function lancamento(string $data, float $valor): object
    {
        return (object) ['data' => Carbon::parse($data), 'valor' => $valor];
    }

    public function test_ano_sem_nenhum_lancamento_retorna_doze_meses_zerados(): void
    {
        $resultado = FluxoCaixaService::calcular(new Collection(), new Collection(), 2026);

        $this->assertCount(12, $resultado['labels']);
        $this->assertSame(array_fill(0, 12, 0.0), $resultado['receitas']);
        $this->assertSame(array_fill(0, 12, 0.0), $resultado['despesas']);
        $this->assertSame(array_fill(0, 12, 0.0), $resultado['saldo']);
        $this->assertSame(array_fill(0, 12, 0.0), $resultado['saldoAcumulado']);
    }

    public function test_soma_receitas_e_despesas_no_mes_correto(): void
    {
        $receitas = new Collection([$this->lancamento('2026-03-10', 1000.0)]);
        $despesas = new Collection([$this->lancamento('2026-03-15', 400.0)]);

        $resultado = FluxoCaixaService::calcular($receitas, $despesas, 2026);

        $this->assertSame(1000.0, $resultado['receitas'][2]);
        $this->assertSame(400.0, $resultado['despesas'][2]);
        $this->assertSame(600.0, $resultado['saldo'][2]);
    }

    public function test_ignora_lancamentos_de_outro_ano(): void
    {
        $receitas = new Collection([$this->lancamento('2025-03-10', 1000.0)]);
        $despesas = new Collection([$this->lancamento('2027-03-15', 400.0)]);

        $resultado = FluxoCaixaService::calcular($receitas, $despesas, 2026);

        $this->assertSame(array_fill(0, 12, 0.0), $resultado['receitas']);
        $this->assertSame(array_fill(0, 12, 0.0), $resultado['despesas']);
    }

    public function test_saldo_acumulado_soma_meses_anteriores_do_mesmo_ano(): void
    {
        $receitas = new Collection([
            $this->lancamento('2026-01-05', 500.0),
            $this->lancamento('2026-02-05', 500.0),
        ]);
        $despesas = new Collection([
            $this->lancamento('2026-01-10', 200.0),
        ]);

        $resultado = FluxoCaixaService::calcular($receitas, $despesas, 2026);

        $this->assertSame(300.0, $resultado['saldoAcumulado'][0]);
        $this->assertSame(800.0, $resultado['saldoAcumulado'][1]);
    }

    public function test_parcelas_contam_no_mes_da_propria_data(): void
    {
        $despesas = new Collection([
            $this->lancamento('2026-01-10', 100.0),
            $this->lancamento('2026-02-10', 100.0),
            $this->lancamento('2026-03-10', 100.0),
        ]);

        $resultado = FluxoCaixaService::calcular(new Collection(), $despesas, 2026);

        $this->assertSame(100.0, $resultado['despesas'][0]);
        $this->assertSame(100.0, $resultado['despesas'][1]);
        $this->assertSame(100.0, $resultado['despesas'][2]);
    }
}
