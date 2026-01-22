<?php

namespace App\Filament\Resources\Products\SatuanResource\Pages;

use App\Filament\Resources\Products\SatuanResource;
use App\Models\Products\Satuan;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSatuans extends ListRecords
{
    protected static string $resource = SatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    // Automatically set the user_id to the currently authenticated user's ID
                    $data['user_id'] = Auth::id();
                    return Satuan::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }

    protected function isTableRecordClickable(): bool
    {
        return false;
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn() => null;
    }
}
