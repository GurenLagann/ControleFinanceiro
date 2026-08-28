<?php

namespace App\Services;

use App\Models\Despesa;
use Illuminate\Support\Collection;

class AssinaturasService
{
    /**
     * Percentual da renda mensal comprometido pelo total de despesas recorrentes.
     */
    public static function percentualDaRenda(float $totalRecorrentes, float $rendaMensal): float
    {
        if ($rendaMensal <= 0) {
            return 0.0;
        }

        return round(($totalRecorrentes / $rendaMensal) * 100, 2);
    }

    /**
     * A partir de qual percentual da renda as assinaturas/recorrentes sao
     * consideradas uma parcela comprometedora do orcamento.
     */
    public static function comprometida(float $percentual): bool
    {
        return $percentual >= 30.0;
    }

    /**
     * Despesas recorrentes ativas, ordenadas da mais cara para a mais barata.
     */
    public function listar(): Collection
    {
        return Despesa::where('recorrente', true)
            ->where('ativo', true)
            ->orderByDesc('valor')
            ->get();
    }

    public function totalMensal(): float
    {
        return (float) $this->listar()->sum('valor');
    }
}
