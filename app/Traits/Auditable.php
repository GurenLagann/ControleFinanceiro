<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable()
    {
        // Log ao criar
        static::created(function ($model) {
            AuditLog::logCreate($model);
        });

        // Log ao atualizar
        static::updating(function ($model) {
            // Guardar valores antigos antes da atualização
            $model->auditOldValues = $model->getOriginal();
        });

        static::updated(function ($model) {
            $oldValues = $model->auditOldValues ?? [];
            AuditLog::logUpdate($model, $oldValues);
        });

        // Log ao deletar
        static::deleted(function ($model) {
            AuditLog::logDelete($model);
        });
    }
}
