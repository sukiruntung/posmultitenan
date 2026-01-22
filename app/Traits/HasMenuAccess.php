<?php

namespace App\Traits;

use App\Models\Accesses\MenuAccess;
use Illuminate\Support\Facades\Cache;

trait HasMenuAccess
{
    public function cacheMenuAccess()
    {
        $cacheKeyHeader = "menu_access_ids:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}";

        // Cek Redis cache
        if (Cache::has($cacheKeyHeader)) {
            return;
        }
        $menuAccesses = MenuAccess::where('user_group_id', $this->user_group_id)->get();
        $menuIds = $menuAccesses->pluck('menu_id')->toArray();
        Cache::put("menu_access_ids:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}", $menuIds, now()->addHours(9));

        foreach ($menuAccesses as $access) {
            $cacheKey = "menu_access:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}:{$access->menu_id}";
            Cache::put($cacheKey, $access, now()->addHours(9));
        }
    }

    public function getCachedMenuAccess(int $menuId)
    {
        return Cache::get("menu_access:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}:{$menuId}");
    }

    public function clearMenuAccessCache()
    {
        $cacheKeyHeader = "menu_access_ids:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}";
        $menuAccesses = MenuAccess::withTrashed()->where('user_group_id', $this->user_group_id)->get();

        foreach ($menuAccesses as $access) {
            $cacheKey = "menu_access:{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}:{$access->menu_id}";
            Cache::forget($cacheKey);
        }
        Cache::forget($cacheKeyHeader);
    }
}
