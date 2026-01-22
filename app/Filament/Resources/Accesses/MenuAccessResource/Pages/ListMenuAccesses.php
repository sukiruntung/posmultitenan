<?php

namespace App\Filament\Resources\Accesses\MenuAccessResource\Pages;

use App\Filament\Resources\Accesses\MenuAccessResource;
use App\Models\Accesses\MenuAccess;
use App\Models\Accesses\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMenuAccesses extends ListRecords
{
    protected static string $resource = MenuAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id();
                    return MenuAccess::create($data);
                })
                ->after(function (MenuAccess $record) {
                    $users = User::where('user_group_id', $record->user_group_id)->get();
                    foreach ($users as $user) {
                        $user->clearMenuAccessCache();
                        $user->cacheMenuAccess();
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
