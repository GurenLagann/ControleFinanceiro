<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Cartao extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'cartoes';

    protected $fillable = [
        'nome',
        'dia_fechamento',
        'dia_vencimento',
        'limite',
        'ativo',
    ];

    protected $casts = [
        'dia_fechamento' => 'integer',
        'dia_vencimento' => 'integer',
        'limite' => 'float',
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'ativo' => true,
    ];

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
