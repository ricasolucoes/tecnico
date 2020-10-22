<?php

namespace Tecnico\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use \Tecnico\Traits\UserHasGroups;
}
