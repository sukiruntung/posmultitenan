<?php

namespace App\Filament\Resources\Accesses\MasterDataAccessResource\Pages;

use App\Filament\Resources\Accesses\MasterDataAccessResource;
use App\Models\Accesses\MasterDataAccess;
use App\Models\Accesses\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMasterDataAccesses extends ListRecords
{
    protected static string $resource = MasterDataAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id(); // tambahkan user_id
                    return MasterDataAccess::create($data);
                })
                ->after(function (MasterDataAccess $record) {
                    $users = User::where('user_group_id', $record->user_group_id)->get();
                    foreach ($users as $user) {
                        $user->clearMasterDataAccessCache();
                        $user->cacheMasterDataAccess();
                    }
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
