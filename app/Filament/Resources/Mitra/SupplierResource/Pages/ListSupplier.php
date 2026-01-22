<?php

namespace App\Filament\Resources\Mitra\SupplierResource\Pages;

use App\Filament\Resources\Mitra\SupplierResource;
use App\Models\Mitra\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSupplier extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    $data['user_id'] = Auth::id();
                    $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
                    return Supplier::create($data);
                    // Custom action logic can be added here if needed
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
