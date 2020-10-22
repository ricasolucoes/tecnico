<?php

namespace Tecnico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Tecnico\Traits\TecnicoGroupTrait;

class TecnicoGroup extends Model
{
    use TecnicoGroupTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $fillable = ['name', 'owner_id'];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('tecnico.groups_table');
    }
}
