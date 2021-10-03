<?php

namespace Tecnico\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

trait TecnicoGroupTrait
{
    /**
     * One-to-Many relation with the invite model.
     * @return mixed
     */
    public function invites()
    {
        return $this->hasMany(Config::get('tecnico.invite_model'), 'group_id', 'id');
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('tecnico.user_model'), Config::get('tecnico.group_user_table'), 'group_id', 'user_id')->withTimestamps();
    }

    /**
     * Has-One relation with the user model.
     * This indicates the owner of the group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        $userModel = Config::get('tecnico.user_model');
        $userKeyName = ( new $userModel() )->getKeyName();

        return $this->belongsTo($userModel, 'owner_id', $userKeyName);
    }

    /**
     * Helper function to determine if a user is part
     * of this group.
     *
     * @param Model $user
     * @return bool
     */
    public function hasUser(Model $user)
    {
        return $this->users()->where($user->getKeyName(), '=', $user->getKey())->first() ? true : false;
    }
}
