<?php

namespace App\Models;

use App\Notifications\AlertaCriado;
use Illuminate\Support\Facades\Notification;
use MongoDB\Laravel\Eloquent\Model;

class Alerta extends Model
{
    protected static function booted(): void
    {
        static::created(function (Alerta $alerta) {
            $email = config('services.notificacoes.email');

            if (! empty($email)) {
                Notification::route('mail', $email)->notify(new AlertaCriado($alerta));
            }
        });
    }

    protected $connection = 'mongodb';
    protected $collection = 'alertas';

    protected $fillable = [
        'titulo',
        'mensagem',
        'tipo', // 'vencimento', 'limite', 'meta', 'lembrete', 'info'
        'data_alerta',
        'referencia_tipo', // 'despesa', 'receita', 'meta'
        'referencia_id',
        'lido',
        'ativo',
    ];

    protected $casts = [
        'data_alerta' => 'date',
        'lido' => 'boolean',
        'ativo' => 'boolean',
    ];

    protected $attributes = [
        'lido' => false,
        'ativo' => true,
    ];

    public function scopeNaoLidos($query)
    {
        return $query->where('lido', false)->where('ativo', true);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
