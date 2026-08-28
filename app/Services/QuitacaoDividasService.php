<?php

namespace App\Services;

use App\Models\Divida;
use Illuminate\Support\Collection;

class QuitacaoDividasService
{
    /**
     * Ordena uma lista de dividas por estrategia de quitacao:
     * - 'snowball': menor valor restante primeiro (motivacao rapida)
     * - 'avalanche': maior taxa de juros mensal primeiro (menos juros pago no total)
     *
     * @param  array<int, array{id: mixed, valor_restante: float, taxa_juros_mensal: ?float}>  $dividas
     * @return array<int, array{id: mixed, valor_restante: float, taxa_juros_mensal: ?float}>
     */
    public static function ordenar(array $dividas, string $estrategia): array
    {
        $ordenadas = $dividas;

        if ($estrategia === 'avalanche') {
            usort($ordenadas, function ($a, $b) {
                $taxaA = $a['taxa_juros_mensal'] ?? 0;
                $taxaB = $b['taxa_juros_mensal'] ?? 0;

                return $taxaB <=> $taxaA ?: $a['valor_restante'] <=> $b['valor_restante'];
            });
        } else {
            usort($ordenadas, fn ($a, $b) => $a['valor_restante'] <=> $b['valor_restante']);
        }

        return array_values($ordenadas);
    }

    /**
     * Monta o plano de quitacao (snowball e avalanche) a partir das dividas
     * ativas/em atraso, com os campos ja calculados de valor restante.
     *
     * @return array{snowball: array, avalanche: array}
     */
    public function planoAtual(?Collection $dividas = null): array
    {
        $dividas ??= Divida::whereIn('status', ['ativa', 'em_atraso'])->get();

        $itens = $dividas->map(function (Divida $divida) {
            $valorPago = collect($divida->pagamentos ?? [])->sum('valor');

            return [
                'id' => (string) $divida->_id,
                'descricao' => $divida->descricao,
                'credor' => $divida->credor,
                'valor_restante' => max(0, (float) $divida->valor_total - $valorPago),
                'taxa_juros_mensal' => $divida->taxa_juros_mensal !== null ? (float) $divida->taxa_juros_mensal : null,
            ];
        })->all();

        return [
            'snowball' => self::ordenar($itens, 'snowball'),
            'avalanche' => self::ordenar($itens, 'avalanche'),
        ];
    }
}
