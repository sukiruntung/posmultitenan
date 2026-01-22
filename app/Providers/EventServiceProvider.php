<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use App\Listeners\CacheMenuAccessOnLogin;
use App\Listeners\ClearCacheOnLogout;
use Illuminate\Auth\Events\Logout;

class EventServiceProvider extends ServiceProvider
{
    // ...existing code...

    protected $listen = [
        // ...existing listeners...
        Login::class => [
            CacheMenuAccessOnLogin::class,
        ],
        Logout::class => [
            ClearCacheOnLogout::class,
        ],
    ];

    // ...existing code...
}
