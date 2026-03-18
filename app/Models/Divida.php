<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\Auditable;

class Divida extends Model
{
    use Auditable;
    protected $connection = 'mongodb';
    protected $collection = 'dividas';

    protected $fillable = [
        'descricao',
        'credor',
        'valor_total',
        'data_inicio',
        'data_vencimento',
        'categoria',
        'status', // ativa, quitada, em_atraso
        'observacoes',
        'pagamentos', // array embedded
    ];

    protected $casts = [
        'valor_total' => 'float',
        'data_inicio' => 'date',
        'data_vencimento' => 'date',
    ];

    protected $attributes = [
        'status' => 'ativa',
        'pagamentos' => [],
    ];

    public function getValorPagoAttribute(): float
    {
        return collect($this->pagamentos ?? [])->sum('valor');
    }

    public function getValorRestanteAttribute(): float
    {
        return max(0, $this->valor_total - $this->valor_pago);
    }

    public function getPercentualPagoAttribute(): float
    {
        if ($this->valor_total <= 0) return 0;
        return min(100, round(($this->valor_pago / $this->valor_total) * 100, 1));
    }

    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativa');
    }

    public function scopeEmAtraso($query)
    {
        return $query->where('status', 'em_atraso');
    }

    public function scopeQuitadas($query)
    {
        return $query->where('status', 'quitada');
    }
}
