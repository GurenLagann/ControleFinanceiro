<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class FluxoCaixaService
{
    /**
     * Calcula o fluxo de caixa mensal de um ano: receitas, despesas, saldo
     * e saldo acumulado por mes. Cada parcela ja e um lancamento com sua
     * propria data, entao entra naturalmente no mes em que vence.
     */
    public static function calcular(Collection $receitas, Collection $despesas, int $ano): array
    {
        $labels = [];
        $totalReceitas = array_fill(0, 12, 0.0);
        $totalDespesas = array_fill(0, 12, 0.0);

        for ($mes = 1; $mes <= 12; $mes++) {
            $labels[] = Carbon::createFromDate($ano, $mes, 1)->translatedFormat('M/y');
        }

        foreach ($receitas as $receita) {
            if ($receita->data && $receita->data->year === $ano) {
                $totalReceitas[$receita->data->month - 1] += $receita->valor;
            }
        }

        foreach ($despesas as $despesa) {
            if ($despesa->data && $despesa->data->year === $ano) {
                $totalDespesas[$despesa->data->month - 1] += $despesa->valor;
            }
        }

        $saldo = [];
        $saldoAcumulado = [];
        $acumulado = 0.0;

        for ($i = 0; $i < 12; $i++) {
            $saldo[$i] = $totalReceitas[$i] - $totalDespesas[$i];
            $acumulado += $saldo[$i];
            $saldoAcumulado[$i] = $acumulado;
        }

        return [
            'ano' => $ano,
            'labels' => $labels,
            'receitas' => $totalReceitas,
            'despesas' => $totalDespesas,
            'saldo' => $saldo,
            'saldoAcumulado' => $saldoAcumulado,
        ];
    }
}
