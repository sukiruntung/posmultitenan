<?php

namespace App\Traits;

use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasDashboardAccess
{

    public function cacheDashboardAccess()
    {
        $user =  $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        $cacheKeyHeader = "dashboard_access_ids:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}";

        // Cek Redis cache
        if (Cache::has($cacheKeyHeader)) {
            return;
        }
        $dashboardAccesses = DashboardAccess::where('user_group_id', $this->user_group_id)
            ->where('outlet_id', $user->userOutlet->outlet_id)
            ->get();
        $systemDashboardIds = $dashboardAccesses->pluck('system_dashboard_id')->toArray();
        Cache::put("dashboard_access_ids:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}", $systemDashboardIds, now()->addHours(9));

        Cache::put("dashboard_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}", $dashboardAccesses, now()->addHours(9));
        foreach ($dashboardAccesses as $access) {
            $cacheKey = "dashboard_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$access->system_dashboard_id}";
            Cache::put($cacheKey, $access, now()->addHours(9));
        }
    }

    public function getCachedDashboardAccess(int $menuId)
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        return Cache::get("dashboard_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$menuId}");
    }
    public  function clearDashboardAccessCache()
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        $query = DashboardAccess::where('user_group_id', $this->user_group_id)
            ->where('outlet_id', $user->userOutlet->outlet_id);
        // Log::info('DashboardAccess SQL', [
        //     'sql' => $query->toSql(),
        //     'bindings' => $query->getBindings(),
        // ]);

        $menuAccesses = $query->get();

        $cacheKeyHeader = "dashboard_access_ids:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}";
        foreach ($menuAccesses as $access) {
            $cacheKey = "dashboard_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$access->system_dashboard_id}";
            Cache::forget($cacheKey);
        }
        Cache::forget($cacheKeyHeader);
        Cache::forget("dashboard_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}");
    }
}
