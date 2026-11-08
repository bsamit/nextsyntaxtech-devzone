<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DevZone Master Switch
    |--------------------------------------------------------------------------
    | Set to false in production if you wish to disable all DevZone routes completely.
    */
    'enabled' => env('DEVZONE_ENABLED', env('DEV_PANEL_ENABLED', true)),

    /*
    |--------------------------------------------------------------------------
    | Dedicated Panel Access Path
    |--------------------------------------------------------------------------
    | The URL prefix used to access the independent developer panel.
    | Example: 'developer-panel' -> http://your-app.com/developer-panel
    */
    'path' => env('DEVZONE_PATH', env('DEV_PANEL_PATH', 'developer-panel')),

    /*
    |--------------------------------------------------------------------------
    | Master Developer Access Key / Token
    |--------------------------------------------------------------------------
    | The secure token or passkey required to access the developer panel.
    | If null and setup_completed is false, visitors are prompted to setup their token.
    */
    'passkey' => env('DEVZONE_KEY', env('DEV_PANEL_KEY', 'amitsaha.cse@gmail.com')),

    /*
    |--------------------------------------------------------------------------
    | Authorized Administrator Email
    |--------------------------------------------------------------------------
    */
    'admin_email' => env('DEVZONE_EMAIL', env('DEV_PANEL_EMAIL', 'amitsaha.cse@gmail.com')),

    /*
    |--------------------------------------------------------------------------
    | Initial Setup Status
    |--------------------------------------------------------------------------
    | When false and passkey is not set, DevZone prompts for first-time passkey setup.
    */
    'setup_completed' => env('DEVZONE_SETUP_COMPLETED', true),

    /*
    |--------------------------------------------------------------------------
    | Session & Security Settings
    |--------------------------------------------------------------------------
    */
    'session_key'        => 'devzone_authenticated_session',
    'session_auth_time'  => 'devzone_authenticated_at',
    'session_timeout'    => 3600, // 1 hour idle session timeout
    'max_login_attempts' => 5,    // Lock out after 5 consecutive failed attempts
    'lockout_minutes'    => 10,   // Lock out for 10 minutes

    /*
    |--------------------------------------------------------------------------
    | Allowed Safe Artisan Commands (Strict Whitelist)
    |--------------------------------------------------------------------------
    | Only safe, predefined cache clearing and optimization commands are permitted.
    | Arbitrary shell execution is strictly disallowed.
    */
    'allowed_commands' => [
        'optimize:clear' => [
            'name'        => 'php artisan optimize:clear',
            'description' => 'Clears all compiled bootstrap files, configs, routes, views, events and application cache.',
            'category'    => 'Optimization',
            'icon'        => 'bx-trash',
            'badge'       => 'Recommended',
            'badge_color' => 'success',
        ],
        'config:clear' => [
            'name'        => 'php artisan config:clear',
            'description' => 'Removes the configuration cache file (bootstrap/cache/config.php).',
            'category'    => 'Cache',
            'icon'        => 'bx-cog',
            'badge'       => 'Config',
            'badge_color' => 'primary',
        ],
        'route:clear' => [
            'name'        => 'php artisan route:clear',
            'description' => 'Removes the route cache file (bootstrap/cache/routes-v7.php).',
            'category'    => 'Cache',
            'icon'        => 'bx-git-merge',
            'badge'       => 'Routing',
            'badge_color' => 'primary',
        ],
        'cache:clear' => [
            'name'        => 'php artisan cache:clear',
            'description' => 'Clears the configured application cache (Redis, File, or Database).',
            'category'    => 'Cache',
            'icon'        => 'bx-layer-minus',
            'badge'       => 'App Cache',
            'badge_color' => 'warning',
        ],
        'view:clear' => [
            'name'        => 'php artisan view:clear',
            'description' => 'Clears all compiled Blade view files in storage/framework/views.',
            'category'    => 'Views',
            'icon'        => 'bx-window-close',
            'badge'       => 'Blade',
            'badge_color' => 'info',
        ],
        'event:clear' => [
            'name'        => 'php artisan event:clear',
            'description' => 'Clears all cached events and listeners in bootstrap/cache.',
            'category'    => 'Events',
            'icon'        => 'bx-bell-off',
            'badge'       => 'Events',
            'badge_color' => 'secondary',
        ],
        'storage:link' => [
            'name'        => 'php artisan storage:link',
            'description' => 'Creates the symbolic link from public/storage to storage/app/public.',
            'category'    => 'Filesystem',
            'icon'        => 'bx-link',
            'badge'       => 'Storage',
            'badge_color' => 'success',
        ],
        'config:cache' => [
            'name'        => 'php artisan config:cache',
            'description' => 'Creates a cache file for faster configuration loading.',
            'category'    => 'Speedup',
            'icon'        => 'bx-check-shield',
            'badge'       => 'Config',
            'badge_color' => 'info',
        ],
        'route:cache' => [
            'name'        => 'php artisan route:cache',
            'description' => 'Creates a route cache file for faster route registration.',
            'category'    => 'Speedup',
            'icon'        => 'bx-map-pin',
            'badge'       => 'Routing',
            'badge_color' => 'info',
        ],
        'view:cache' => [
            'name'        => 'php artisan view:cache',
            'description' => 'Compiles all Blade views for improved response times.',
            'category'    => 'Speedup',
            'icon'        => 'bx-file',
            'badge'       => 'Views',
            'badge_color' => 'info',
        ],
    ],
];
