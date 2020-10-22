<?php

namespace Tecnico\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Class UsedByGroups.
 */
trait UsedByGroups
{
    /**
     * Boot the global scope.
     */
    protected static function bootUsedByGroups()
    {
        static::addGlobalScope('group', function (Builder $builder) {
            static::groupGuard();

            $builder->where($builder->getQuery()->from.'.group_id', auth()->user()->currentGroup->getKey());
        });

        static::saving(function (Model $model) {
            if (! isset($model->group_id)) {
                static::groupGuard();

                $model->group_id = auth()->user()->currentGroup->getKey();
            }
        });
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeAllGroups(Builder $query)
    {
        return $query->withoutGlobalScope('group');
    }

    /**
     * @return mixed
     */
    public function group()
    {
        return $this->belongsTo(Config::get('tecnico.group_model'));
    }

    /**
     * @throws Exception
     */
    protected static function groupGuard()
    {
        if (auth()->guest() || ! auth()->user()->currentGroup) {
            throw new Exception('No authenticated user with selected group present.');
        }
    }
}
