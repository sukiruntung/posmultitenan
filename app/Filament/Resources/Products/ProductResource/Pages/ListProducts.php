<?php

namespace App\Filament\Resources\Products\ProductResource\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Products\Merk;
use App\Models\Products\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    if (!empty($data['merk_id'])) {
                        $merk = Merk::find($data['merk_id']);
                        if ($merk) {
                            $merkName = str_replace(' ', '_', $merk->merk_name);
                            $data['product_catalog'] =
                                $data['product_catalog'] . '_' . $merkName;
                        }
                    }
                    $data['product_slug'] = Str::slug($data['product_name'] . '-' . $data['satuan_id']);
                    $data['user_id'] = Auth::id();
                    return Product::create($data);
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),
        ];
    }
}
