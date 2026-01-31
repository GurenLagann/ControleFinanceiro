<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Despesa extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'despesas';

    protected $fillable = [
        'descricao',
        'valor',
        'data',
        'categoria',
        'recorrente',
        'frequencia',
        'dia_vencimento',
        'ativo',
        'origem_recorrente_id',
        // Campos de parcelamento
        'parcelado',
        'parcela_atual',
        'total_parcelas',
        'valor_total',
        'grupo_parcela_id',
    ];

    protected $casts = [
        'valor' => 'float',
        'valor_total' => 'float',
        'data' => 'date',
        'recorrente' => 'boolean',
        'parcelado' => 'boolean',
        'dia_vencimento' => 'integer',
        'parcela_atual' => 'integer',
        'total_parcelas' => 'integer',
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'recorrente' => false,
        'parcelado' => false,
        'ativo' => true,
    ];

    public function scopeRecorrentes($query)
    {
        return $query->where('recorrente', true)->where('ativo', true);
    }

    public function scopeParceladas($query)
    {
        return $query->where('parcelado', true);
    }

    public function getDescricaoCompletaAttribute()
    {
        if ($this->parcelado && $this->total_parcelas > 1) {
            return $this->descricao . ' (' . $this->parcela_atual . '/' . $this->total_parcelas . ')';
        }
        return $this->descricao;
    }
}
