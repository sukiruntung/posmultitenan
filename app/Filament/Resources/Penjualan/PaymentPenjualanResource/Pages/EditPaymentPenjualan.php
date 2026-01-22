<?php

namespace App\Filament\Resources\Penjualan\PaymentPenjualanResource\Pages;

use App\Filament\Resources\Penjualan\PaymentPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentPenjualan extends EditRecord
{
    protected static string $resource = PaymentPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
