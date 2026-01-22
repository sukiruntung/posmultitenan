<?php

namespace App\Filament\Widgets;

use App\Models\Products\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MinStockTable extends BaseWidget
{
    protected static ?string $heading = 'Laporan Produk dibawah Stok Minimal';
    protected static ?int $sort = 4;
    protected static function user()
    {
        return auth()->user();
    }
    public static function canView(): bool
    {
        $user = static::user();
        $access = $user->getCachedDashboardAccess(4);
        return $access ? $access->can_view : false;
    }
    public function table(Table $table): Table
    {
        $outletId = Auth::user()->userOutlet->outlet_id;

        $query = Cache::remember('min_stock_products_outlet_' . $outletId, now()->addHours(3), function () use ($outletId) {
            return Product::where('outlet_id', $outletId)
                ->withSum('productStock', 'stock')
                ->havingRaw('product_stock_sum_stock <= product_minstock')
                ->orderBy('product_stock_sum_stock', 'asc')
                ->groupby('id')
                ->get();
        });

        return $table
            ->query(
                Product::where('outlet_id', $outletId)
                    ->whereIn('id', $query->pluck('id'))
                    ->withSum('productStock', 'stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_stock_sum_stock')
                    ->label('Total Stok')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('product_minstock')
                    ->label('Minimal Stok')
                    ->sortable(),
            ]);
    }
}
