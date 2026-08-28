<?php

namespace App\Services;

use App\Models\Despesa;
use Carbon\Carbon;

class InsightsService
{
    protected const LIMIAR_RELEVANCIA = 15.0;

    /**
     * Gera um insight comparando o gasto atual de uma categoria com sua
     * media anterior. Retorna null quando a variacao nao e relevante.
     *
     * @return array{texto: string, relevancia: float}|null
     */
    public static function gerarInsight(string $categoria, float $atual, float $mediaAnterior): ?array
    {
        if ($mediaAnterior <= 0) {
            if ($atual <= 0) {
                return null;
            }

            return [
                'texto' => "Você começou a gastar em {$categoria} este mês (R$ " . number_format($atual, 2, ',', '.') . ').',
                'relevancia' => 999.0,
            ];
        }

        $variacao = round((($atual - $mediaAnterior) / $mediaAnterior) * 100, 1);

        if (abs($variacao) < self::LIMIAR_RELEVANCIA) {
            return null;
        }

        $direcao = $variacao > 0 ? 'a mais' : 'a menos';

        return [
            'texto' => "Você gastou " . abs($variacao) . "% {$direcao} em {$categoria} este mês (R$ "
                . number_format($atual, 2, ',', '.') . ' vs média de R$ ' . number_format($mediaAnterior, 2, ',', '.') . ').',
            'relevancia' => abs($variacao),
        ];
    }

    /**
     * @param  array<int, array{texto: string, relevancia: float}>  $insights
     * @return array<int, array{texto: string, relevancia: float}>
     */
    public static function ordenarPorRelevancia(array $insights): array
    {
        usort($insights, fn ($a, $b) => $b['relevancia'] <=> $a['relevancia']);

        return $insights;
    }

    /**
     * Gera ate $limite insights reais, comparando o mes atual com a media
     * dos $mesesComparacao meses anteriores, por categoria de despesa.
     *
     * @return array<int, string>
     */
    public function gerar(int $limite = 3, int $mesesComparacao = 3, ?Carbon $referencia = null): array
    {
        $referencia ??= Carbon::now();

        $inicioMesAtual = $referencia->copy()->startOfMonth();
        $fimMesAtual = $referencia->copy()->endOfMonth();

        $inicioAnterior = $referencia->copy()->subMonths($mesesComparacao)->startOfMonth();
        $fimAnterior = $referencia->copy()->subMonth()->endOfMonth();

        $gastoAtualPorCategoria = Despesa::whereBetween('data', [$inicioMesAtual, $fimMesAtual])
            ->get()
            ->groupBy(fn ($d) => $d->categoria ?: 'Sem categoria')
            ->map(fn ($grupo) => $grupo->sum('valor'));

        $gastoAnteriorPorCategoria = Despesa::whereBetween('data', [$inicioAnterior, $fimAnterior])
            ->get()
            ->groupBy(fn ($d) => $d->categoria ?: 'Sem categoria')
            ->map(fn ($grupo) => $grupo->sum('valor') / $mesesComparacao);

        $categorias = $gastoAtualPorCategoria->keys()->merge($gastoAnteriorPorCategoria->keys())->unique();

        $insights = [];

        foreach ($categorias as $categoria) {
            $insight = self::gerarInsight(
                $categoria,
                (float) ($gastoAtualPorCategoria[$categoria] ?? 0),
                (float) ($gastoAnteriorPorCategoria[$categoria] ?? 0)
            );

            if ($insight) {
                $insights[] = $insight;
            }
        }

        $insights = self::ordenarPorRelevancia($insights);

        return array_column(array_slice($insights, 0, $limite), 'texto');
    }
}
