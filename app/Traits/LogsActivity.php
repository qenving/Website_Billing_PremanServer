<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            ActivityLog::log(
                'created',
                class_basename($model) . ' created',
                $model,
                ['attributes' => $model->getAttributes()]
            );
        });

        static::updated(function ($model) {
            ActivityLog::log(
                'updated',
                class_basename($model) . ' updated',
                $model,
                [
                    'old' => $model->getOriginal(),
                    'new' => $model->getAttributes(),
                ]
            );
        });

        static::deleted(function ($model) {
            ActivityLog::log(
                'deleted',
                class_basename($model) . ' deleted',
                $model,
                ['attributes' => $model->getAttributes()]
            );
        });
    }
}
