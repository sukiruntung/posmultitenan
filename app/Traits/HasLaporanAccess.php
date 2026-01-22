<?php

namespace App\Traits;

use App\Models\Accesses\LaporanAccess;
use Illuminate\Support\Facades\Cache;

trait HasLaporanAccess
{
    public function cacheLaporanAccess()
    {
        $cacheKeyHeader = "laporan_cache_user_group_{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}";

        // Cek Redis cache
        if (Cache::has($cacheKeyHeader)) {
            return;
        }
        $laporanAccesses = LaporanAccess::where('user_group_id', $this->user_group_id)
            ->whereHas('laporan', function ($query) {
                $query->where('outlet_id', $this->userOutlet->outlet_id);
            })
            ->with([
                'laporan' => fn($query) =>
                $query->where('outlet_id', $this->userOutlet->outlet_id),
            ])
            ->get();

        $laporanData = [];
        foreach ($laporanAccesses as $lap) {
            $params = [];
            if (! empty($lap->laporan->params)) {
                $decoded = is_array($lap->laporan->params) ? $lap->laporan->params : json_decode($lap->laporan->params, true);
                if (is_array($decoded)) {
                    $params = $decoded;
                }
            }
            $laporanData[$lap->id] = [
                'nama' => $lap->laporan->laporan_kode . ' - ' . $lap->laporan->laporan_name,
                'params' => $params,
                'path' => $lap->laporan->path,
                'is_excel' => $lap->laporan->is_excel,
                'path_excel' => $lap->laporan->path_excel,
            ];
        }
        Cache::put($cacheKeyHeader, $laporanData, now()->addHours(9));
    }

    public function getCachedLaporanAccess(int $userGroupId)
    {
        return Cache::get("laporan_cache_user_group_{$userGroupId}:outlet_id:{$this->userOutlet->outlet_id}");
    }

    public function clearLaporanAccessCache()
    {
        $cacheKey = "laporan_cache_user_group_{$this->user_group_id}:outlet_id:{$this->userOutlet->outlet_id}";
        Cache::forget($cacheKey);
    }
}
