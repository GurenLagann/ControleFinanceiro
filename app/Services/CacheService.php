<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Receita;
use App\Models\Despesa;

class CacheService
{
    const CACHE_TTL = 300; // 5 minutos

    /**
     * Obter receitas com cache
     */
    public static function getReceitas()
    {
        return Cache::remember('receitas_all', self::CACHE_TTL, function () {
            return Receita::orderBy('data', 'desc')->get();
        });
    }

    /**
     * Obter despesas com cache
     */
    public static function getDespesas()
    {
        return Cache::remember('despesas_all', self::CACHE_TTL, function () {
            return Despesa::orderBy('data', 'desc')->get();
        });
    }

    /**
     * Obter receitas recorrentes com cache
     */
    public static function getReceitasRecorrentes()
    {
        return Cache::remember('receitas_recorrentes', self::CACHE_TTL, function () {
            return Receita::where('recorrente', true)->where('ativo', true)->get();
        });
    }

    /**
     * Obter despesas recorrentes com cache
     */
    public static function getDespesasRecorrentes()
    {
        return Cache::remember('despesas_recorrentes', self::CACHE_TTL, function () {
            return Despesa::where('recorrente', true)->where('ativo', true)->get();
        });
    }

    /**
     * Obter despesas parceladas com cache
     */
    public static function getDespesasParceladas()
    {
        return Cache::remember('despesas_parceladas', self::CACHE_TTL, function () {
            return Despesa::where('parcelado', true)
                ->orderBy('data', 'asc')
                ->get()
                ->groupBy('grupo_parcela_id');
        });
    }

    /**
     * Limpar todos os caches de financas
     */
    public static function clearAll()
    {
        Cache::forget('receitas_all');
        Cache::forget('despesas_all');
        Cache::forget('receitas_recorrentes');
        Cache::forget('despesas_recorrentes');
        Cache::forget('despesas_parceladas');
    }

    /**
     * Limpar cache de receitas
     */
    public static function clearReceitas()
    {
        Cache::forget('receitas_all');
        Cache::forget('receitas_recorrentes');
    }

    /**
     * Limpar cache de despesas
     */
    public static function clearDespesas()
    {
        Cache::forget('despesas_all');
        Cache::forget('despesas_recorrentes');
        Cache::forget('despesas_parceladas');
    }
}
