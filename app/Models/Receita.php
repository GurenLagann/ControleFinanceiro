<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Receita extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'receitas';

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
    ];

    protected $casts = [
        'valor' => 'float',
        'data' => 'date',
        'recorrente' => 'boolean',
        'dia_vencimento' => 'integer',
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'recorrente' => false,
        'ativo' => true,
    ];

    public function scopeRecorrentes($query)
    {
        return $query->where('recorrente', true)->where('ativo', true);
    }

    public function scopeNaoRecorrentes($query)
    {
        return $query->where(function($q) {
            $q->where('recorrente', false)->orWhereNull('recorrente');
        });
    }
}
