<?php

namespace App\Filament\Resources\Accesses\SystemDashboardResource\Pages;

use App\Filament\Resources\Accesses\SystemDashboardResource;
use App\Models\Accesses\SystemDashboard;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSystemDashboards extends ListRecords
{
    protected static string $resource = SystemDashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id(); // tambahkan user_id
                    return SystemDashboard::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
