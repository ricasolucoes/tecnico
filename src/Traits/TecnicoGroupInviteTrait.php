<?php

namespace Tecnico\Traits;

use Illuminate\Support\Facades\Config;

trait TecnicoGroupInviteTrait
{
    /**
     * Has-One relations with the group model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Config::get('tecnico.group_model'), 'id', 'group_id');
    }

    /**
     * Has-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Config::get('tecnico.user_model'), 'email', 'email');
    }

    /**
     * Has-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function inviter(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Config::get('tecnico.user_model'), 'id', 'user_id');
    }
}
