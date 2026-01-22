<?php

namespace App\Filament\Resources\Accesses\UserGroupResource\Pages;

use App\Filament\Resources\Accesses\UserGroupResource;
use App\Models\Accesses\UserGroup;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUserGroups extends ListRecords
{
    protected static string $resource = UserGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id(); // tambahkan user_id
                    return UserGroup::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
