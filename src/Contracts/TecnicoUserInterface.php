<?php

namespace Tecnico\Contracts;

interface TecnicoUserInterface
{
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups();

    /**
     * has-one relation with the current selected group model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentGroup();

    /**
     * One-to-Many relation with the invite model.
     * @return mixed
     */
    public function invites();

    /**
     * Returns if the user owns a group.
     *
     * @return bool
     */
    public function isOwner();

    /**
     * Returns if the user owns the given group.
     *
     * @param mixed $group
     * @return bool
     */
    public function isOwnerOfGroup($group);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $group
     * @param array $pivotData
     */
    public function attachGroup($group, $pivotData = []);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $group
     */
    public function detachGroup($group);

    /**
     * Attach multiple groups to a user.
     *
     * @param mixed $groups
     */
    public function attachGroups($groups);

    /**
     * Detach multiple groups from a user.
     *
     * @param mixed $groups
     */
    public function detachGroups($groups);

    /**
     * Switch the current group of the user.
     *
     * @param object|array|int $group
     * @throws ModelNotFoundException
     * @throws UserNotInGroupException
     */
    public function switchGroup($group);
}
