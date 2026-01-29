<?php

namespace App\Traits;

use App\Models\Accesses\MasterDataAccess;
use App\Models\Accesses\User;
use App\Scopes\ForAuthUserGroupScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasMasterDataAccess
{

    public function cacheMasterDataAccess()
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        $cacheKeyHeader = "masterdata_access_ids:{$this->user_group_id}:outlet_id:{$user->userOutlet->outlet_id}";

        // Cek Redis cache
        if (Cache::has($cacheKeyHeader)) {
            return;
        }

        $menuAccesses = MasterDataAccess::with([
            'masterData.masterDataGroup'
        ])->withGlobalScope('auth_group', new ForAuthUserGroupScope())
            ->whereNull('deleted_at')
            ->get();

        $masterDataIds = $menuAccesses->pluck('master_data_id')->toArray();
        Cache::put("masterdata_access_ids:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}", $masterDataIds, now()->addHours(9));
        // MasterDataAccess::where('user_group_id', $this->user_group_id)->get();

        foreach ($menuAccesses as $access) {
            $cacheKey = "masterdata_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$access->master_data_id}";
            Cache::put($cacheKey, $access, now()->addHours(9));
        }
    }

    public function getCachedMasterDataAccess(int $menuId)
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        return Cache::get("masterdata_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$menuId}");
    }
    public function getCachedMasterDataAccessId()
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        return Cache::get("masterdata_access_ids:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}");
    }

    public function clearMasterDataAccessCache()
    {
        $user = $this->authUserWithOutlet();
        if (!$user) {
            return;
        }
        $menuAccesses = MasterDataAccess::withTrashed()
            ->where('user_group_id', $this->user_group_id)
            ->where('outlet_id', $user->userOutlet->outlet_id)
            ->get();

        $cacheKeyHeader = "masterdata_access_ids:{$this->user_group_id}";
        foreach ($menuAccesses as $access) {
            $cacheKey = "masterdata_access:{$this->user_group_id}:outlet_{$user->userOutlet->outlet_id}:{$access->master_data_id}";
            Cache::forget($cacheKey);
        }
        Cache::forget($cacheKeyHeader);
    }
}
