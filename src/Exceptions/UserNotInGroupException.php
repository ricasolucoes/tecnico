<?php

namespace Tecnico\Exceptions;

use RuntimeException;

class UserNotInGroupException extends RuntimeException
{
    /**
     * Name of the affected group.
     *
     * @var string
     */
    protected $group;

    /**
     * Set the affected group.
     *
     * @param  string   $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        $this->message = "The user is not in the group {$group}";

        return $this;
    }

    /**
     * Get the affected group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
}
