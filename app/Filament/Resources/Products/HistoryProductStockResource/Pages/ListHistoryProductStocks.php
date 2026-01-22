<?php

namespace App\Filament\Resources\Products\HistoryProductStockResource\Pages;

use App\Filament\Resources\Products\HistoryProductStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoryProductStocks extends ListRecords
{
    protected static string $resource = HistoryProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
