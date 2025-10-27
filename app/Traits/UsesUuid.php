<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UsesUuid
{
    /**
     * Boot the model
     */
    protected static function bootUsesUuid()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the primary key type
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Get the incrementing flag
     */
    public function getIncrementing()
    {
        return false;
    }
}

