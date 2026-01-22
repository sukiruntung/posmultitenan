<?php

namespace App\Filament\Widgets;

use App\Models\Products\ProductStock;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EdTable extends BaseWidget
{
    protected static ?string $heading = 'Daftar Produk ED / Kurang dari 1 Bulan';
    protected static ?int $sort = 5;
    protected static function user()
    {
        return auth()->user();
    }
    public static function canView(): bool
    {
        $user = static::user();
        $access = $user->getCachedDashboardAccess(5);
        return $access ? $access->can_view : false;
    }
    public function table(Table $table): Table
    {
        $outletId = Auth::user()->userOutlet->outlet_id;

        $cachedIds = Cache::remember('ed_products_' . now()->format('Y-m-d') . '_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            return ProductStock::query()
                ->whereHas('product', function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                })
                ->whereDate('product_stock_ed', '<=', Carbon::now()->addMonth())
                ->where('stock', '>', 0)
                ->pluck('id');
        });

        $query = ProductStock::query()
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*', 'products.product_name')
            ->where('products.outlet_id', $outletId)
            ->whereIn('product_stocks.id', $cachedIds)
            ->orderBy('products.product_name', 'asc');

        return $table
            ->query(
                $query
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.product_name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_stock_ed')
                    ->label('ED')
                    ->sortable()
                    ->badge()
                    ->color('danger')

            ]);
    }
}
