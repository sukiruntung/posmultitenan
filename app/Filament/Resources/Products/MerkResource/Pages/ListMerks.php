<?php

namespace App\Filament\Resources\Products\MerkResource\Pages;

use App\Filament\Resources\Products\MerkResource;
use App\Models\Products\Merk;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMerks extends ListRecords
{
    protected static string $resource = MerkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    // Automatically set the user_id to the currently authenticated user's ID
                    $data['user_id'] = Auth::id();
                    return Merk::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
