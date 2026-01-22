<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class CacheMenuAccessOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        if ($user && method_exists($user, 'cacheMenuAccess')) {
            $user->cacheMenuAccess();
        }
        if ($user && method_exists($user, 'cacheMasterDataAccess')) {
            $user->cacheMasterDataAccess();
        }
        if ($user && method_exists($user, 'cacheDashboardAccess')) {
            $user->cacheDashboardAccess();
        }
        if ($user && method_exists($user, 'cacheLaporanAccess')) {
            $user->cacheLaporanAccess();
        }
    }
}
