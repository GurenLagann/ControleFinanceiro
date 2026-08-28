<?php

namespace App\Services;

use App\Models\Despesa;
use Carbon\Carbon;

class ReservaEmergenciaService
{
    /**
     * Faixa recomendada de reserva de emergencia: de 3x a 6x o gasto mensal medio.
     *
     * @return array{minimo: float, ideal: float}
     */
    public static function calcularFaixa(float $gastoMensalMedio): array
    {
        $gastoMensalMedio = max(0.0, $gastoMensalMedio);

        return [
            'minimo' => round($gastoMensalMedio * 3, 2),
            'ideal' => round($gastoMensalMedio * 6, 2),
        ];
    }

    /**
     * Media de despesas mensais dos ultimos N meses (baseado em dados reais),
     * usada como proxy do "gasto mensal" para a faixa de reserva.
     */
    public function gastoMensalMedio(int $meses = 3, ?Carbon $referencia = null): float
    {
        $referencia ??= Carbon::now();

        $inicio = $referencia->copy()->subMonths($meses - 1)->startOfMonth();
        $fim = $referencia->copy()->endOfMonth();

        $total = Despesa::whereBetween('data', [$inicio, $fim])->sum('valor');

        return $meses > 0 ? round($total / $meses, 2) : 0.0;
    }

    /**
     * Faixa recomendada de reserva de emergencia com base nos dados reais.
     *
     * @return array{minimo: float, ideal: float, gasto_mensal_medio: float}
     */
    public function faixaAtual(int $meses = 3, ?Carbon $referencia = null): array
    {
        $media = $this->gastoMensalMedio($meses, $referencia);

        return self::calcularFaixa($media) + ['gasto_mensal_medio' => $media];
    }
}
