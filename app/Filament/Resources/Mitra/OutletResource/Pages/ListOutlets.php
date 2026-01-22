<?php

namespace App\Filament\Resources\Mitra\OutletResource\Pages;

use App\Filament\Resources\Mitra\OutletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListOutlets extends ListRecords
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['user_id'] = Auth::id();
                    return $data;
                }),
        ];
    }
}
