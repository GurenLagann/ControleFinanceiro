<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Categoria extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'categorias';

    protected $fillable = [
        'nome',
        'cor',
        'icone',
        'tipo', // 'receita', 'despesa', 'ambos'
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'ativo' => true,
        'tipo' => 'ambos',
    ];

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeParaReceitas($query)
    {
        return $query->whereIn('tipo', ['receita', 'ambos']);
    }

    public function scopeParaDespesas($query)
    {
        return $query->whereIn('tipo', ['despesa', 'ambos']);
    }
}
