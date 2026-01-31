<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audit_logs';

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'user_ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Registrar uma ação de criação
     */
    public static function logCreate($model)
    {
        return self::createLog($model, 'create', [], $model->toArray());
    }

    /**
     * Registrar uma ação de atualização
     */
    public static function logUpdate($model, array $oldValues = [])
    {
        return self::createLog($model, 'update', $oldValues, $model->toArray());
    }

    /**
     * Registrar uma ação de exclusão
     */
    public static function logDelete($model)
    {
        return self::createLog($model, 'delete', $model->toArray(), []);
    }

    /**
     * Criar o registro de log
     */
    protected static function createLog($model, string $action, array $oldValues, array $newValues)
    {
        return self::create([
            'model_type' => get_class($model),
            'model_id' => (string) $model->_id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_ip' => request()->ip() ?? 'console',
            'user_agent' => request()->userAgent() ?? 'console',
            'created_at' => now(),
        ]);
    }

    /**
     * Obter descrição legível da ação
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'Criação',
            'update' => 'Atualização',
            'delete' => 'Exclusão',
            default => $this->action,
        };
    }

    /**
     * Obter nome do modelo sem namespace
     */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }

    /**
     * Obter cor do badge baseado na ação
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->action) {
            'create' => 'success',
            'update' => 'warning',
            'delete' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope para filtrar por ação
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
