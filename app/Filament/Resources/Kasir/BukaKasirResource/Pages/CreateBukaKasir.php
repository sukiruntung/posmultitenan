<?php

namespace App\Filament\Resources\Kasir\BukaKasirResource\Pages;

use App\Filament\Resources\Kasir\BukaKasirResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;

class CreateBukaKasir extends CreateRecord
{
    protected static string $resource = BukaKasirResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['kasir_id'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
