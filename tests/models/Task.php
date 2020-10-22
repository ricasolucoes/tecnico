<?php

namespace Tecnico\Tests;

use Illuminate\Database\Eloquent\Model;
use Tecnico\Traits\UsedByGroups;

class Task extends Model
{
    use UsedByGroups;
}
