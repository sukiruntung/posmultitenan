<?php

namespace App\Filament\Resources\Accesses\UserResource\Pages;

use App\Filament\Resources\Accesses\UserResource;
use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\MasterDataAccess;
use App\Models\Accesses\MenuAccess;
use App\Models\Accesses\Outlet;
use App\Models\Accesses\User;
use App\Models\Accesses\UserOutlet;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        $data['user_id'] = Auth::id();
                        $role = $data['role'] ?? 'staff';
                        $outletName = $data['outlet_name'] ?? null;
                        $outletAddress = $data['outlet_address'] ?? null;

                        unset($data['role'], $data['outlet_name'], $data['outlet_address']);

                        $user = User::create($data);

                        // Jika role owner, buat outlet baru
                        if ($role === 'owner' && $outletName) {
                            $outlet = Outlet::create([
                                'outlet_name' => $outletName,
                                'outlet_address' => $outletAddress,
                                'owner_user_id' => $user->id,
                                'user_id' => Auth::id(),
                            ]);

                            UserOutlet::create([
                                'user_id' => $user->id,
                                'outlet_id' => $outlet->id,
                                'role' => $role,
                            ]);

                            // Insert master_data_access untuk owner
                            $ownerMasterDataIds = [1, 2, 3, 6, 9, 10, 11, 12, 13, 14, 15, 17, 19, 20];
                            $ownerMenuIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                            $ownerDashboardIds = [1, 2, 3];
                            foreach ($ownerMasterDataIds as $masterDataId) {
                                MasterDataAccess::create([
                                    'outlet_id' => $outlet->id,
                                    'master_data_id' => $masterDataId,
                                    'user_group_id' => $user->user_group_id,
                                    'can_view' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_delete' => true,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            foreach ($ownerMenuIds as $menuId) {
                                MenuAccess::create([
                                    'outlet_id' => $outlet->id,
                                    'menu_id' => $menuId,
                                    'user_group_id' => $user->user_group_id,
                                    'can_view' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_delete' => true,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            foreach ($ownerDashboardIds as $dashboardId) {
                                DashboardAccess::create([
                                    'outlet_id' => $outlet->id,
                                    'system_dashboard_id' => $dashboardId,
                                    'user_group_id' => $user->user_group_id,
                                    'can_view' => true,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                        } elseif ($role === 'staff') {
                            // Untuk staff, gunakan outlet user yang login
                            UserOutlet::create([
                                'user_id' => $user->id,
                                'outlet_id' => Auth::user()->userOutlet->outlet_id,
                                'role' => $role,
                            ]);

                            // Insert master_data_access untuk staff
                            $staffMasterDataIds = [1, 2, 3, 6, 10, 11, 12, 13, 14, 15];
                            $ownerMenuIds = [1];
                            foreach ($staffMasterDataIds as $masterDataId) {
                                MasterDataAccess::create([
                                    'outlet_id' => Auth::user()->userOutlet->outlet_id,
                                    'master_data_id' => $masterDataId,
                                    'user_group_id' => $user->user_group_id,
                                    'can_view' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_delete' => false,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            foreach ($ownerMenuIds as $menuId) {
                                MenuAccess::create([
                                    'outlet_id' => Auth::user()->userOutlet->outlet_id,
                                    'menu_id' => $menuId,
                                    'user_group_id' => $user->user_group_id,
                                    'can_view' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_delete' => true,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                        } else {
                            // Untuk role lain (admin), gunakan outlet user yang login
                            UserOutlet::create([
                                'user_id' => $user->id,
                                'outlet_id' => Auth::user()->userOutlet->outlet_id,
                                'role' => $role,
                            ]);
                        }

                        return $user;
                    });
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),

        ];
    }
}
