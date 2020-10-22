<?php

namespace Tecnico\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UserInvitedToGroup
{
    use SerializesModels;

    /**
     * @type Model
     */
    protected $invite;

    public function __construct($invite)
    {
        $this->invite = $invite;
    }

    /**
     * @return Model
     */
    public function getInvite()
    {
        return $this->invite;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->invite->group_id;
    }
}
