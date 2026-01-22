<?php

namespace App\Filament\Resources\Products\ProductStockResource\Pages;

use App\Exports\LaporanProductStockExport;
use App\Filament\Resources\Products\ProductStockResource;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ListProductStocks extends ListRecords
{
    protected static string $resource = ProductStockResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        // dd($data['product_stock_sn']);
                        $data['user_id'] = Auth::id();

                        $productStock = ProductStock::create($data);

                        ProductStockHistories::create([
                            'product_stock_id' => $productStock->id,
                            'qty_masuk' => $data['stock'],
                            'stock_akhir' => $data['stock'],
                            // 'harga_beli' => $data['harga_beli'],
                            // 'total_harga_beli' => $data['harga_beli'] * $data['stock'],
                            'jenis' => 'stock awal',
                            'user_id' => Auth::id(),
                        ]);
                        return $productStock;
                    });
                }),
            Actions\Action::make('import')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(route('filament.admin.pages.master-data')),

        ];
    }
}
