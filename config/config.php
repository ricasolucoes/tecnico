<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auth Model
    |--------------------------------------------------------------------------
    |
    | This is the Auth model used by Tecnico.
    |
    */
    'user_model' => config('auth.providers.users.model', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Tecnico users Table
    |--------------------------------------------------------------------------
    |
    | This is the users table name used by Tecnico.
    |
    */
    'users_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Tecnico Group Model
    |--------------------------------------------------------------------------
    |
    | This is the Group model used by Tecnico to create correct relations.  Update
    | the group if it is in a different namespace.
    |
    */
    'group_model' => Tecnico\TecnicoGroup::class,

    /*
    |--------------------------------------------------------------------------
    | Tecnico groups Table
    |--------------------------------------------------------------------------
    |
    | This is the groups table name used by Tecnico to save groups to the database.
    |
    */
    'groups_table' => 'groups',

    /*
    |--------------------------------------------------------------------------
    | Tecnico group_user Table
    |--------------------------------------------------------------------------
    |
    | This is the group_user table used by Tecnico to save assigned groups to the
    | database.
    |
    */
    'group_user_table' => 'group_user',

    /*
    |--------------------------------------------------------------------------
    | User Foreign key on Tecnico's group_user Table (Pivot)
    |--------------------------------------------------------------------------
    */
    'user_foreign_key' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Tecnico Group Invite Model
    |--------------------------------------------------------------------------
    |
    | This is the Group Invite model used by Tecnico to create correct relations.
    | Update the group if it is in a different namespace.
    |
    */
    'invite_model' => Tecnico\GroupInvite::class,

    /*
    |--------------------------------------------------------------------------
    | Tecnico group invites Table
    |--------------------------------------------------------------------------
    |
    | This is the group invites table name used by Tecnico to save sent/pending
    | invitation into groups to the database.
    |
    */
    'group_invites_table' => 'group_invites',
];
