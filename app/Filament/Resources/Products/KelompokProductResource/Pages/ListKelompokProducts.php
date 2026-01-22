<?php

namespace App\Filament\Resources\Products\KelompokProductResource\Pages;

use App\Filament\Resources\Products\KelompokProductResource;
use App\Models\Products\KelompokProduct;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListKelompokProducts extends ListRecords
{
    protected static string $resource = KelompokProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id(); // tambahkan user_id
                    return KelompokProduct::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
