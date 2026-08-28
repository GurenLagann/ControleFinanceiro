<?php

namespace App\Services;

use App\Models\Cartao;
use App\Models\Despesa;
use Carbon\Carbon;

class FaturaService
{
    /**
     * Calcula o ciclo de fatura (inicio e fim) que contem a data de referencia,
     * dado o dia de fechamento do cartao. O dia da referencia igual ao dia de
     * fechamento ainda conta como parte do ciclo que fecha nesse dia.
     *
     * @return array{inicio: Carbon, fim: Carbon}
     */
    public static function calcularCiclo(int $diaFechamento, Carbon $referencia): array
    {
        $diaFechamento = max(1, min(31, $diaFechamento));

        $fimCandidato = self::diaAjustado($referencia->copy(), $diaFechamento);

        if ($referencia->day <= $fimCandidato->day) {
            $fim = $fimCandidato;
            $mesInicio = $referencia->copy()->subMonthNoOverflow();
        } else {
            $fim = self::diaAjustado($referencia->copy()->addMonthNoOverflow(), $diaFechamento);
            $mesInicio = $referencia->copy();
        }

        $inicio = self::diaAjustado($mesInicio, $diaFechamento)->addDay();

        return [
            'inicio' => $inicio->startOfDay(),
            'fim' => $fim->copy()->endOfDay(),
        ];
    }

    protected static function diaAjustado(Carbon $data, int $dia): Carbon
    {
        return $data->copy()->day(min($dia, $data->daysInMonth));
    }

    /**
     * Total da fatura de um cartao no ciclo que contem a data de referencia.
     *
     * @return array{ciclo: array{inicio: Carbon, fim: Carbon}, total: float}
     */
    public function faturaAtual(Cartao $cartao, ?Carbon $referencia = null): array
    {
        $referencia ??= Carbon::now();

        $ciclo = self::calcularCiclo((int) $cartao->dia_fechamento, $referencia);

        $total = Despesa::where('cartao_id', (string) $cartao->_id)
            ->whereBetween('data', [$ciclo['inicio'], $ciclo['fim']])
            ->sum('valor');

        return ['ciclo' => $ciclo, 'total' => (float) $total];
    }
}
