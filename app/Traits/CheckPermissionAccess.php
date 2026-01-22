<?php

namespace App\Traits;

trait CheckPermissionAccess
{
    public static function user()
    {
        return auth()->user();
    }
    protected static function checkDashboardAccess(string $type, int $menuId): bool
    {
        $access = static::user()->getCachedDashboardAccess($menuId);

        return $access ? (bool) $access->{$type} : false;
    }
    protected static function checkMenuAccess(string $type, int $menuId): bool
    {
        $access = static::user()->getCachedMenuAccess($menuId);

        return $access ? (bool) $access->{$type} : false;
    }
    protected static function checkMasterDataAccess(string $type, int $menuId): bool
    {
        $access = static::user()->getCachedMasterDataAccess($menuId);

        return $access ? (bool) $access->{$type} : false;
    }
    protected static function checkLaporanAccess(int $userGroupId): ?array
    {
        $access = static::user()->getCachedLaporanAccess($userGroupId);

        if (! $access) {
            return []; // tidak ada akses
        }
        // dd($access);
        // Ubah JSON string → array PHP
        return $access;
    }
}
