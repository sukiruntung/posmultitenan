<?php

namespace App\Filament\Resources\Products\ProductStockResource\Pages;

use App\Filament\Resources\Products\ProductStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductStock extends EditRecord
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
