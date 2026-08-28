<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Despesa;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrcamentoService
{
    /**
     * Calcula o percentual gasto de um orcamento e seu status:
     * 'ok' (< 80%), 'atencao' (80% a 100%) ou 'excedido' (>= 100%).
     */
    public static function calcularProgresso(float $orcamento, float $gasto): array
    {
        if ($orcamento <= 0) {
            return ['percentual' => 0.0, 'status' => 'ok'];
        }

        $percentual = round(($gasto / $orcamento) * 100, 2);

        $status = match (true) {
            $percentual >= 100 => 'excedido',
            $percentual >= 80 => 'atencao',
            default => 'ok',
        };

        return ['percentual' => $percentual, 'status' => $status];
    }

    /**
     * Progresso do orcamento mensal de cada categoria de despesa que tenha
     * um orcamento_mensal definido, com base nos gastos do mes atual.
     */
    public function progressoPorCategoria(?Carbon $mesReferencia = null): Collection
    {
        $mesReferencia ??= Carbon::now();
        $inicioMes = $mesReferencia->copy()->startOfMonth();
        $fimMes = $mesReferencia->copy()->endOfMonth();

        $categorias = Categoria::ativas()
            ->paraDespesas()
            ->whereNotNull('orcamento_mensal')
            ->where('orcamento_mensal', '>', 0)
            ->get();

        return $categorias->map(function (Categoria $categoria) use ($inicioMes, $fimMes) {
            $gasto = Despesa::where('categoria', $categoria->nome)
                ->whereBetween('data', [$inicioMes, $fimMes])
                ->sum('valor');

            $progresso = self::calcularProgresso((float) $categoria->orcamento_mensal, (float) $gasto);

            return [
                'categoria' => $categoria->nome,
                'cor' => $categoria->cor,
                'icone' => $categoria->icone,
                'orcamento' => (float) $categoria->orcamento_mensal,
                'gasto' => (float) $gasto,
                'percentual' => $progresso['percentual'],
                'status' => $progresso['status'],
            ];
        })->values();
    }
}
