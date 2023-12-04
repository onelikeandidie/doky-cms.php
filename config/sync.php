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
            'name' => env('SYNC_GIT_NAME', 'Doky Sync'),
            'email' => env('SYNC_GIT_EMAIL', 'doky@example.com'),
            'gpgsign' => env('SYNC_GIT_GPGSIGN', false),
            'gpgsign_key' => env('SYNC_GIT_GPGSIGN_KEY'),
            // You can use either ssh_key or access_token, but not both
            'ssh_key' => env('SYNC_GIT_SSH_KEY'),
            'access_token' => env('SYNC_GIT_ACCESS_TOKEN'),
        ],
    ],
];
