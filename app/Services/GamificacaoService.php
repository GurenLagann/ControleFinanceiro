<?php

namespace App\Services;

use App\Models\Despesa;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GamificacaoService
{
    /**
     * Calcula o streak (dias consecutivos com pelo menos um lançamento),
     * contando a partir da data de referência com 1 dia de tolerância:
     * se o último lançamento foi ontem, o streak ainda é considerado ativo.
     *
     * @param  iterable<mixed>  $datas  Datas (Carbon, string ou DateTime) de receitas/despesas.
     */
    public static function calcularStreak(iterable $datas, ?Carbon $referencia = null): int
    {
        $referencia = ($referencia ?? Carbon::now())->copy()->startOfDay();

        $dias = collect($datas)
            ->filter()
            ->map(fn ($data) => Carbon::parse($data)->startOfDay())
            ->unique(fn (Carbon $data) => $data->toDateString())
            ->sortByDesc(fn (Carbon $data) => $data->toDateString())
            ->values();

        if ($dias->isEmpty()) {
            return 0;
        }

        $maisRecente = $dias->first();

        if ($maisRecente->diffInDays($referencia) > 1) {
            return 0;
        }

        $streak = 0;
        $cursor = $maisRecente->copy();

        foreach ($dias as $dia) {
            if ($dia->equalTo($cursor)) {
                $streak++;
                $cursor = $cursor->copy()->subDay();
            } elseif ($dia->lessThan($cursor)) {
                break;
            }
        }

        return $streak;
    }

    /**
     * Streak atual do usuário, a partir das receitas e despesas já lançadas.
     */
    public function streakAtual(?Collection $receitas = null, ?Collection $despesas = null): int
    {
        $receitas ??= Receita::all(['data']);
        $despesas ??= Despesa::all(['data']);

        $datas = $receitas->pluck('data')->merge($despesas->pluck('data'));

        return self::calcularStreak($datas);
    }
}
