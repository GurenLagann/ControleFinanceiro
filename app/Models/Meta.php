<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Meta extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'metas';

    protected $fillable = [
        'titulo',
        'descricao',
        'valor_alvo',
        'valor_atual',
        'data_inicio',
        'data_fim',
        'categoria',
        'tipo', // 'economia', 'limite_gasto', 'receita'
        'ativo',
    ];

    protected $casts = [
        'valor_alvo' => 'float',
        'valor_atual' => 'float',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'ativo' => true,
        'valor_atual' => 0,
    ];

    public function getProgressoAttribute()
    {
        if ($this->valor_alvo <= 0) return 0;
        return min(100, ($this->valor_atual / $this->valor_alvo) * 100);
    }

    public function getDiasRestantesAttribute()
    {
        if (!$this->data_fim) return null;
        return max(0, now()->diffInDays($this->data_fim, false));
    }

    public function getStatusAttribute()
    {
        if ($this->progresso >= 100) return 'concluida';
        if ($this->dias_restantes === 0) return 'vencida';
        if ($this->dias_restantes <= 7) return 'urgente';
        return 'em_andamento';
    }
}
