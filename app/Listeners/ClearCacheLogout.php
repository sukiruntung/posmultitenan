<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;

class ClearCacheLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (!$user) {
            return;
        }

        // Bersihkan cache laporan access
        // $user->clearLaporanAccessCache();
        // $user->clearMenuAccessCache();
        // $user->clearMasterDataAccessCache();
        // $user->clearDashboardAccessCache();

        // Kalau kamu juga punya cache menu, bisa tambahkan:
        // Cache::forget("menu_access_{$user->user_group_id}");
    }
}
