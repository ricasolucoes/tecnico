<?php

namespace Tecnico;

use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Application;
use Tecnico\Events\UserInvitedToGroup;

class Tecnico
{
    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new Tecnico instance.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the currently authenticated user or null.
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    /**
     * Invite an email adress to a group.
     * Either provide a email address or an object with an email property.
     *
     * If no group is given, the current_group_id will be used instead.
     *
     * @param string|User $user
     * @param null|Group $group
     * @param callable $success
     * @return GroupInvite
     * @throws \Exception
     */
    public function inviteToGroup($user, $group = null, callable $success = null)
    {
        if (is_null($group)) {
            $group = $this->user()->current_group_id;
        } elseif (is_object($group)) {
            $group = $group->getKey();
        } elseif (is_array($group)) {
            $group = $group['id'];
        }

        if (is_object($user) && isset($user->email)) {
            $email = $user->email;
        } elseif (is_string($user)) {
            $email = $user;
        } else {
            throw new \Exception('The provided object has no "email" attribute and is not a string.');
        }

        $invite = $this->app->make(Config::get('tecnico.invite_model'));
        $invite->user_id = $this->user()->getKey();
        $invite->group_id = $group;
        $invite->type = 'invite';
        $invite->email = $email;
        $invite->accept_token = md5(uniqid(microtime()));
        $invite->deny_token = md5(uniqid(microtime()));
        $invite->save();

        if (! is_null($success)) {
            event(new UserInvitedToGroup($invite));
            $success($invite);
        }

        return $invite;
    }

    /**
     * Checks if the given email address has a pending invite for the
     * provided Group.
     * @param $email
     * @param Group|array|int $group
     * @return bool
     */
    public function hasPendingInvite($email, $group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }
        if (is_array($group)) {
            $group = $group['id'];
        }

        return $this->app->make(Config::get('tecnico.invite_model'))->where('email', '=', $email)->where('group_id', '=', $group)->first() ? true : false;
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getInviteFromAcceptToken($token)
    {
        return $this->app->make(Config::get('tecnico.invite_model'))->where('accept_token', '=', $token)->first();
    }

    /**
     * @param GroupInvite $invite
     */
    public function acceptInvite(GroupInvite $invite)
    {
        $this->user()->attachGroup($invite->group);
        $invite->delete();
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getInviteFromDenyToken($token)
    {
        return $this->app->make(Config::get('tecnico.invite_model'))->where('deny_token', '=', $token)->first();
    }

    /**
     * @param GroupInvite $invite
     */
    public function denyInvite(GroupInvite $invite)
    {
        $invite->delete();
    }
}
