<?php

return [
    // Driver to use
    'driver' => env('SYNC_DRIVER', 'git'),
    // Path to store the synced files
    'path' => env('SYNC_PATH', storage_path('app/sync')),
    // Configuration for drivers
    'drivers' => [
        'git' => [
            'bin' => env('SYNC_GIT_BIN', 'git'),
            // Path inside the repository to sync, if '*' then the whole repository will be synced
            'path' => env('SYNC_GIT_PATH', '*'),
            'repo' => env('SYNC_GIT_REPOSITORY'),
            'branch' => env('SYNC_GIT_BRANCH', 'master'),
        ],
    ],
];
