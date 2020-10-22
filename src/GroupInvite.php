<?php

namespace Tecnico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Tecnico\Traits\TecnicoGroupInviteTrait;

class GroupInvite extends Model
{
    use TecnicoGroupInviteTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('tecnico.group_invites_table');
    }
}
