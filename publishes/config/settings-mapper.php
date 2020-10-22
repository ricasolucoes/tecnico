<?php

return [

    /**
     * Features
     */
    'features' => [
        'point_types' => [
            'active' => true,
            'model' => \Gamer\Models\Routine::class,
        ],
        'tasks' => [
            'active' => true,
            'model' => \Gamer\Models\Task::class,
        ],
        'metas' => [
            'active' => true,
            'model' => \Gamer\Models\Meta::class,
        ],
    ],
    /**
     * Extensions
     */
    'extensions' => [
        'routines' => [
            'active' => true,
            'model' => \Gamer\Models\Routine::class,
        ],
        'tasks' => [
            'active' => true,
            'model' => \Gamer\Models\Task::class,
        ],
        'metas' => [
            'active' => true,
            'model' => \Gamer\Models\Meta::class,
        ],
    ],

    // Attributes Database Tables
    'services' => [

        'pointagram' => env('SERVICES_POINTAGRAM_KEY', null),

    ],

    // Attributes Database Tables
    'tables' => [

        'teste' => 'teste',

    ],

];

