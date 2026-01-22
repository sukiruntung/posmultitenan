<?php

namespace App\Filament\Resources\Kasir\BukaKasirResource\Pages;

use App\Filament\Resources\Kasir\BukaKasirResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBukaKasir extends EditRecord
{
    protected static string $resource = BukaKasirResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
    protected function getHeaderActions(): array
    {
        return [];
    }

    // protected function getTitle(): string
    // {
    //     return '';
    // }
}
