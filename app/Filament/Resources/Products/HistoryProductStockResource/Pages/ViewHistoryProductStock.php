<?php

namespace App\Filament\Resources\Products\HistoryProductStockResource\Pages;

use App\Filament\Resources\Products\HistoryProductStockResource;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class ViewHistoryProductStock extends Page
{
    protected static string $resource = HistoryProductStockResource::class;

    protected static string $view = 'filament.pages.product.view-history-product-stock';

    public $record;
    public ProductStock $product;
    public $histories;

    public function mount($record)
    {
        // --- CACHE PRODUCT INFO ---
        $productKey = "product_stock_info:$record";

        $this->product = Cache::remember($productKey, now()->addHours(2), function () use ($record) {
            return ProductStock::with('product')->findOrFail($record);
        });

        // --- CACHE HISTORY STOCK ---
        $historyKey = "product_stock_histories:$record";

        $this->histories = Cache::remember($historyKey, now()->addMinutes(30), function () use ($record) {
            return ProductStockHistories::where('product_stock_id', $record)
                ->latest()
                ->limit(50)
                ->get();
        });
    }

    public function getHeading(): string
    {
        return false;
    }
}
