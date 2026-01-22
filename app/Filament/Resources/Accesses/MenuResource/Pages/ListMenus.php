<?php

namespace App\Filament\Resources\Accesses\MenuResource\Pages;

use App\Filament\Resources\Accesses\MenuResource;
use App\Models\Accesses\Menu;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id();
                    return Menu::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
