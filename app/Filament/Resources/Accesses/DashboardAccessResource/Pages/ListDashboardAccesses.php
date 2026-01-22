<?php

namespace App\Filament\Resources\Accesses\DashboardAccessResource\Pages;

use App\Filament\Resources\Accesses\DashboardAccessResource;
use App\Models\Accesses\DashboardAccess;
use App\Models\Accesses\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDashboardAccesses extends ListRecords
{
    protected static string $resource = DashboardAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id(); // tambahkan user_id
                    return DashboardAccess::create($data);
                })
                ->after(function (DashboardAccess $record) {
                    $users = User::where('user_group_id', $record->user_group_id)->get();
                    foreach ($users as $user) {
                        $user->clearDashboardAccessCache();
                        $user->cacheDashboardAccess();
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
