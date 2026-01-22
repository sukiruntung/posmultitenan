<?php

namespace App\Traits;

use App\Models\Accesses\DashboardAccess;
use Illuminate\Support\Facades\Cache;

trait HasDashboardAccess
{
    public function cacheDashboardAccess()
    {
        $cacheKeyHeader = "dashboard_access_ids:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}";

        // Cek Redis cache
        if (Cache::has($cacheKeyHeader)) {
            return;
        }
        $dashboardAccesses = DashboardAccess::where('user_group_id', $this->user_group_id)
            ->where('outlet_id', $this->userOutlet->outlet_id)
            ->get();
        $systemDashboardIds = $dashboardAccesses->pluck('system_dashboard_id')->toArray();
        Cache::put("dashboard_access_ids:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}", $systemDashboardIds, now()->addHours(9));

        Cache::put("dashboard_access:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}", $dashboardAccesses, now()->addHours(9));
        foreach ($dashboardAccesses as $access) {
            $cacheKey = "dashboard_access:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}:{$access->system_dashboard_id}";
            Cache::put($cacheKey, $access, now()->addHours(9));
        }
    }

    public function getCachedDashboardAccess(int $menuId)
    {
        return Cache::get("dashboard_access:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}:{$menuId}");
    }
    public  function clearDashboardAccessCache()
    {
        $menuAccesses = DashboardAccess::withTrashed()
            ->where('user_group_id', $this->user_group_id)
            ->where('outlet_id', $this->userOutlet->outlet_id)
            ->get();
        $cacheKeyHeader = "dashboard_access_ids:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}";
        foreach ($menuAccesses as $access) {
            $cacheKey = "dashboard_access:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}:{$access->system_dashboard_id}";
            Cache::forget($cacheKey);
        }
        Cache::forget($cacheKeyHeader);
        Cache::forget("dashboard_access:{$this->user_group_id}:outlet_{$this->userOutlet->outlet_id}");
    }
}
