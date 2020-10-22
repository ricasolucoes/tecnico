# Tecnico

This package supports Laravel 6 and above.

[![Latest Version](https://img.shields.io/packagist/v/ricasolucoes/tecnico.svg)](https://github.com/ricasolucoes/tecnico/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
![Build Status](https://github.com/ricasolucoes/tecnico/workflows/run-tests/badge.svg)
[![codecov.io](https://codecov.io/github/ricasolucoes/tecnico/coverage.svg?branch=master)](https://codecov.io/github/ricasolucoes/tecnico?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a2a26e55-bfc7-49a9-933b-72ca7c245034/mini.png)](https://insight.sensiolabs.com/projects/a2a26e55-bfc7-49a9-933b-72ca7c245034)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ricasolucoes/tecnico/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ricasolucoes/tecnico/?branch=master)

Tecnico is the fastest and easiest method to add a User / Group association with Invites to your **Laravel 6+** project.

## Installation

    composer require ricasolucoes/tecnico

The `Tecnico` Facade will be auto discovered by Laravel automatically.

## Configuration

To publish Tecnico's configuration and migration files, run the `vendor:publish` command.

```bash
php artisan vendor:publish --provider="Tecnico\TecnicoServiceProvider"
```

This will create a `tecnico.php` in your config directory.
The default configuration should work just fine for you, but you can take a look at it, if you want to customize the table / model names Tecnico will use.

### User relation to groups

Run the `migration` command, to generate all tables needed for Tecnico.
**If your users are stored in a different table other than `users` be sure to modify the published migration.**

```bash
php artisan migrate
```

After the migration, 3 new tables will be created:

- groups &mdash; stores group records
- group_user &mdash; stores [many-to-many](http://laravel.com/docs/5.1/eloquent-relationships#many-to-many) relations between users and groups
- group_invites &mdash; stores pending invites for email addresses to groups

You will also notice that a new column `current_group_id` has been added to your users table.
This column will define the Group, the user is currently assigned to.

### Models

#### Group

Create a Group model inside `app/Group.php` using the following example:

```php
<?php namespace App;

use Tecnico\TecnicoGroup;

class Group extends TecnicoGroup
{
}
```

The `Group` model has two main attributes:

- `owner_id` &mdash; Reference to the User model that owns this Group.
- `name` &mdash; Human readable name for the Group.

The `owner_id` is an optional attribute and is nullable in the database.

When extending TecnicoGroup, remember to change the `group_model` variable in `config/tecnico.php` to your new model. For instance: `'group_model' => App\Group::class`

#### User

Add the `UserHasGroups` trait to your existing User model:

```php
<?php namespace App;

use Tecnico\Traits\UserHasGroups;

class User extends Model {

    use UserHasGroups; // Add this trait to your model
}
```

This will enable the relation with `Group` and add the following methods `groups()`, `ownedGroups()` `currentGroup()`, `invites()`, `isGroupOwner()`, `isOwnerOfGroup($group)`, `attachGroup($group, $pivotData = [])`, `detachGroup($group)`, `attachGroups($groups)`, `detachGroups($groups)`, `switchGroup($group)` within your `User` model.

Don't forget to dump composer autoload

```bash
composer dump-autoload
```

### Middleware

If you would like to use the middleware to protect to current group owner then just add the middleware provider to your `app\Http\Kernel.php` file.

```php
    protected $routeMiddleware = [
        ...
        'groupowner' => \Tecnico\Middleware\GroupOwner::class,
        ...
    ];
```

Afterwards you can use the `groupowner` middleware in your routes file like so.

```php
Route::get('/owner', function(){
    return "Owner of current group.";
})->middleware('auth', 'groupowner');
```

Now only if the authenticated user is the owner of the current group can access that route.

> This middleware is aimed to protect routes where only the owner of the group can edit/create/delete that model

**And you are ready to go.**

## Usage

### Scaffolding

The easiest way to give your new Laravel project Group abilities is by using the `make:tecnico` command.

```bash
php artisan make:tecnico
```

This command will create all views, routes and controllers to make your new project group-ready.

Out of the box, the following parts will be created for you:

- Group listing
- Group creation / editing / deletion
- Invite new members to groups

Imagine it as a the `make:auth` command for Tecnico.

To get started, take a look at the new installed `/groups` route in your project.

### Basic concepts

Let's start by creating two different Groups.

```php
$group    = new Group();
$group->owner_id = User::where('username', '=', 'sebastian')->first()->getKey();
$group->name = 'My awesome group';
$group->save();

$myOtherCompany = new Group();
$myOtherCompany->owner_id = User::where('username', '=', 'marcel')->first()->getKey();
$myOtherCompany->name = 'My other awesome group';
$myOtherCompany->save();
```

Now thanks to the `UserHasGroups` trait, assigning the Groups to the user is uber easy:

```php
$user = User::where('username', '=', 'sebastian')->first();

// group attach alias
$user->attachGroup($group, $pivotData); // First parameter can be a Group object, array, or id

// or eloquent's original technique
$user->groups()->attach($group->id); // id only
```

By using the `attachGroup` method, if the User has no Groups assigned, the `current_group_id` column will automatically be set.

### Get to know my group(s)

The currently assigned Group of a user can be accessed through the `currentGroup` relation like this:

```php
echo "I'm currently in group: " . Auth::user()->currentGroup->name;
echo "The group owner is: " . Auth::user()->currentGroup->owner->username;

echo "I also have these groups: ";
print_r( Auth::user()->groups );

echo "I am the owner of these groups: ";
print_r( Auth::user()->ownedGroups );

echo "My group has " . Auth::user()->currentGroup->users->count() . " users.";
```

The `Group` model has access to these methods:

- `invites()` &mdash; Returns a many-to-many relation to associated invitations.
- `users()` &mdash; Returns a many-to-many relation with all users associated to this group.
- `owner()` &mdash; Returns a one-to-one relation with the User model that owns this group.
- `hasUser(User $user)` &mdash; Helper function to determine if a user is a groupmember

### Group owner

If you need to check if the User is a group owner (regardless of the current group) use the `isGroupOwner()` method on the User model.

```php
if( Auth::user()->isGroupOwner() )
{
    echo "I'm a group owner. Please let me pay more.";
}
```

Additionally if you need to check if the user is the owner of a specific group, use:

```php
$group = Auth::user()->currentGroup;
if( Auth::user()->isOwnerOfGroup( $group ) )
{
    echo "I'm a specific group owner. Please let me pay even more.";
}
```

The `isOwnerOfGroup` method also allows an array or id as group parameter.

### Switching the current group

If your Users are members of multiple groups you might want to give them access to a `switch group` mechanic in some way.

This means that the user has one "active" group, that is currently assigned to the user. All other groups still remain attached to the relation!

Glad we have the `UserHasGroups` trait.

```php
try {
    Auth::user()->switchGroup( $group_id );
    // Or remove a group association at all
    Auth::user()->switchGroup( null );
} catch( UserNotInGroupException $e )
{
    // Given group is not allowed for the user
}
```

Just like the `isOwnerOfGroup` method, `switchGroup` accepts a Group object, array, id or null as a parameter.

### Inviting others

The best group is of no avail if you're the only group member.

To invite other users to your groups, use the `Tecnico` facade.

```php
Tecnico::inviteToGroup( $email, $group, function( $invite )
{
    // Send email to user / let them know that they got invited
});
```

You can also send invites by providing an object with an `email` property like:

```php
$user = Auth::user();

Tecnico::inviteToGroup( $user , $group, function( $invite )
{
    // Send email to user / let them know that they got invited
});
```

This method will create a `GroupInvite` model and return it in the callable third parameter.

This model has these attributes:

- `email` &mdash; The email that was invited.
- `accept_token` &mdash; Unique token used to accept the invite.
- `deny_token` &mdash; Unique token used to deny the invite.

In addition to these attributes, the model has these relations:

- `user()` &mdash; one-to-one relation using the `email` as a unique identifier on the User model.
- `group()` &mdash; one-to-one relation return the Group, that invite was aiming for.
- `inviter()` &mdash; one-to-one relation return the User, that created the invite.

**Note:**
The `inviteToGroup` method will **not** check if the given email already has a pending invite. To check for pending invites use the `hasPendingInvite` method on the `Tecnico` facade.

Example usage:

```php
if( !Tecnico::hasPendingInvite( $request->email, $request->group) )
{
    Tecnico::inviteToGroup( $request->email, $request->group, function( $invite )
    {
                // Send email to user
    });
} else {
    // Return error - user already invited
}
```

### Accepting invites

Once you invited other users to join your group, in order to accept the invitation use the `Tecnico` facade once again.

```php
$invite = Tecnico::getInviteFromAcceptToken( $request->token ); // Returns a TecnicoInvite model or null

if( $invite ) // valid token found
{
    Tecnico::acceptInvite( $invite );
}
```

The `acceptInvite` method does two thing:

- Call `attachGroup` with the invite-group on the currently authenticated user.
- Delete the invitation afterwards.

### Denying invites

Just like accepting invites:

```php
$invite = Tecnico::getInviteFromDenyToken( $request->token ); // Returns a TecnicoInvite model or null

if( $invite ) // valid token found
{
    Tecnico::denyInvite( $invite );
}
```

The `denyInvite` method is only responsible for deleting the invitation from the database.

### Attaching/Detaching/Invite Events

If you need to run additional processes after attaching/detaching a group from a user or inviting a user, you can Listen for these events:

```php
\Tecnico\Events\UserJoinedGroup

\Tecnico\Events\UserLeftGroup

\Tecnico\Events\UserInvitedToGroup
```

In your `EventServiceProvider` add your listener(s):

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    ...
    \Tecnico\Events\UserJoinedGroup::class => [
        App\Listeners\YourJoinedGroupListener::class,
    ],
    \Tecnico\Events\UserLeftGroup::class => [
        App\Listeners\YourLeftGroupListener::class,
    ],
    \Tecnico\Events\UserInvitedToGroup::class => [
        App\Listeners\YourUserInvitedToGroupListener::class,
    ],
];
```

The UserJoinedGroup and UserLeftGroup event exposes the User and Group's ID. In your listener, you can access them like so:

```php
<?php

namespace App\Listeners;

use Tecnico\Events\UserJoinedGroup;

class YourJoinedGroupListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserJoinedGroup  $event
     * @return void
     */
    public function handle(UserJoinedGroup $event)
    {
        // $user = $event->getUser();
        // $groupId = $event->getGroupId();

        // Do something with the user and group ID.
    }
}
```

The UserInvitedToGroup event contains an invite object which could be accessed like this:

```php
<?php

namespace App\Listeners;

use Tecnico\Events\UserInvitedToGroup;

class YourUserInvitedToGroupListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserInvitedToGroup  $event
     * @return void
     */
    public function handle(UserInvitedToGroup $event)
    {
        // $user = $event->getInvite()->user;
        // $groupId = $event->getGroupId();

        // Do something with the user and group ID.
    }
}
```

### Limit Models to current Group

If your models are somehow limited to the current group you will find yourself writing this query over and over again: `Model::where('group_id', auth()->user()->currentGroup->id)->get();`.

To automate this process, you can let your models use the `UsedByGroups` trait. This trait will automatically append the current group id of the authenticated user to all queries and will also add it to a field called `group_id` when saving the models.

**Note:**

> This assumes that the model has a field called `group_id`

#### Usage

```php
use Tecnico\Traits\UsedByGroups;

class Task extends Model
{
    use UsedByGroups;
}
```

When using this trait, all queries will append `WHERE group_id=CURRENT_TEAM_ID`.
If theres a place in your app, where you really want to retrieve all models, no matter what group they belong to, you can use the `allGroups` scope.

**Example:**

```php
// gets all tasks for the currently active group of the authenticated user
Task::all();

// gets all tasks from all groups globally
Task::allGroups()->get();
```

## License

Tecnico is free software distributed under the terms of the MIT license.

'Marvel Avengers' image licensed under [Creative Commons 2.0](https://creativecommons.org/licenses/by/2.0/) - Photo from [W_Minshull](https://www.flickr.com/photos/23950335@N07/8251484285/in/photostream/)
