<?php

namespace Tecnico\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Tecnico\Events\UserJoinedGroup;
use Tecnico\Events\UserLeftGroup;
use Tecnico\Exceptions\UserNotInGroupException;

trait UserHasGroups
{
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Config::get('tecnico.group_model'), Config::get('tecnico.group_user_table'), 'user_id', 'group_id')->withTimestamps();
    }

    /**
     * has-one relation with the current selected group model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentGroup()
    {
        return $this->hasOne(Config::get('tecnico.group_model'), 'id', 'current_group_id');
    }

    /**
     * @return mixed
     */
    public function ownedGroups()
    {
        return $this->groups()->where('owner_id', '=', $this->getKey());
    }

    /**
     * One-to-Many relation with the invite model.
     * @return mixed
     */
    public function invites()
    {
        return $this->hasMany(Config::get('tecnico.invite_model'), 'email', 'email');
    }

    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootUserHasGroups()
    {
        static::deleting(function (Model $user) {
            if (! method_exists(Config::get('tecnico.user_model'), 'bootSoftDeletes')) {
                $user->groups()->sync([]);
            }

            return true;
        });
    }

    /**
     * Returns if the user owns a group.
     *
     * @return bool
     */
    public function isOwner()
    {
        return ($this->groups()->where('owner_id', '=', $this->getKey())->first()) ? true : false;
    }

    /**
     * Wrapper method for "isOwner".
     *
     * @return bool
     */
    public function isGroupOwner()
    {
        return $this->isOwner();
    }

    /**
     * @param $group
     * @return mixed
     */
    protected function retrieveGroupId($group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }
        if (is_array($group) && isset($group['id'])) {
            $group = $group['id'];
        }

        return $group;
    }

    /**
     * Returns if the user owns the given group.
     *
     * @param mixed $group
     * @return bool
     */
    public function isOwnerOfGroup($group)
    {
        $group_id = $this->retrieveGroupId($group);

        return ($this->groups()
            ->where('owner_id', $this->getKey())
            ->where('group_id', $group_id)->first()
        ) ? true : false;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $group
     * @param array $pivotData
     * @return $this
     */
    public function attachGroup($group, $pivotData = [])
    {
        $group = $this->retrieveGroupId($group);
        /*
         * If the user has no current group,
         * use the attached one
         */
        if (is_null($this->current_group_id)) {
            $this->current_group_id = $group;
            $this->save();

            if ($this->relationLoaded('currentGroup')) {
                $this->load('currentGroup');
            }
        }

        // Reload relation
        $this->load('groups');

        if (! $this->groups->contains($group)) {
            $this->groups()->attach($group, $pivotData);

            event(new UserJoinedGroup($this, $group));

            if ($this->relationLoaded('groups')) {
                $this->load('groups');
            }
        }

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $group
     * @return $this
     */
    public function detachGroup($group)
    {
        $group = $this->retrieveGroupId($group);
        $this->groups()->detach($group);

        event(new UserLeftGroup($this, $group));

        if ($this->relationLoaded('groups')) {
            $this->load('groups');
        }

        /*
         * If the user has no more groups,
         * unset the current_group_id
         */
        if ($this->groups()->count() === 0 || $this->current_group_id === $group) {
            $this->current_group_id = null;
            $this->save();

            if ($this->relationLoaded('currentGroup')) {
                $this->load('currentGroup');
            }
        }

        return $this;
    }

    /**
     * Attach multiple groups to a user.
     *
     * @param mixed $groups
     * @return $this
     */
    public function attachGroups($groups)
    {
        foreach ($groups as $group) {
            $this->attachGroup($group);
        }

        return $this;
    }

    /**
     * Detach multiple groups from a user.
     *
     * @param mixed $groups
     * @return $this
     */
    public function detachGroups($groups)
    {
        foreach ($groups as $group) {
            $this->detachGroup($group);
        }

        return $this;
    }

    /**
     * Switch the current group of the user.
     *
     * @param object|array|int $group
     * @return $this
     * @throws ModelNotFoundException
     * @throws UserNotInGroupException
     */
    public function switchGroup($group)
    {
        if ($group !== 0 && $group !== null) {
            $group = $this->retrieveGroupId($group);
            $groupModel = Config::get('tecnico.group_model');
            $groupObject = ( new $groupModel() )->find($group);
            if (! $groupObject) {
                $exception = new ModelNotFoundException();
                $exception->setModel($groupModel);
                throw $exception;
            }
            if (! $groupObject->users->contains($this->getKey())) {
                $exception = new UserNotInGroupException();
                $exception->setGroup($groupObject->name);
                throw $exception;
            }
        }
        $this->current_group_id = $group;
        $this->save();

        if ($this->relationLoaded('currentGroup')) {
            $this->load('currentGroup');
        }

        return $this;
    }
}
