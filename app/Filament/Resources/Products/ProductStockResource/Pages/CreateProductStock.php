<?php

namespace App\Filament\Resources\Products\ProductStockResource\Pages;

use App\Filament\Resources\Products\ProductStockResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductStock extends CreateRecord
{
    protected static string $resource = ProductStockResource::class;
}
