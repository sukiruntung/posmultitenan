<?php

namespace App\Filament\Pages;

use App\Models\Accesses\MasterDataAccess;
use App\Models\Accesses\Menu;
use App\Models\Accesses\MenuAccess;
use App\Scopes\ForAuthUserGroupScope;
use App\Traits\HasMasterDataAccess;
use Filament\Pages\Page;

class MasterData extends Page
{
    use HasMasterDataAccess;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.master-data';
    protected static ?int $navigationSort = 2;
    protected static function user()
    {
        return auth()->user();
    }
    public static function shouldRegisterNavigation(): bool
    {
        $user = static::user();
        $access = $user->getCachedMenuAccess(1);
        return $access ? $access->can_view : false;
    }
    public static function canAccess(): bool
    {
        return true;
        // return static::user()->hasMenuAccesses('1', static::user()->user_group_id, 'can_view');
    }
    public function getViewData(): array
    {
        $user = static::user();
        $masterData = collect();
        $masterDataIds = $user->getCachedMasterDataAccessId();
        foreach ($masterDataIds as $id) {
            if ($cachedData = $user->getCachedMasterDataAccess($id)) {
                $masterData->push($cachedData);
            }
        }
        return [
            'masterData' => $masterData
        ];
    }
}
