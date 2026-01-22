<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\HistoryProductStockResource\Pages;
use App\Filament\Resources\Products\HistoryProductStockResource\RelationManagers;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;

class HistoryProductStockResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 7;
    protected static ?string $model = ProductStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationLabel = 'History Product Stock';

    protected static ?int $navigationSort = 2;
    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMasterDataAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMasterDataAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        return static::checkMasterDataAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkMasterDataAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMasterDataAccess('can_delete', static::$menuId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.product_name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product_stock_sn')
                    ->label('SN')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($record) => $record->stock . ' ' . ($record->product->satuan->satuan_name ?? '')),
                TextColumn::make('updated_at')
                    ->datetime('d M Y H:i')
                    ->sortable(),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->modalHeading('History Product Stock')
                    ->modalWidth('3xl')
                    ->modalContent(function ($record) {
                        $productKey = "product_stock_info:$record->id";

                        $product = Cache::remember($productKey, now()->addHours(2), function () use ($record) {
                            return ProductStock::with('product')->findOrFail($record->id);
                        });

                        // --- CACHE HISTORY STOCK ---
                        $historyKey = "product_stock_histories:$record->id";

                        $histories = Cache::remember($historyKey, now()->addMinutes(30), function () use ($record) {
                            return ProductStockHistories::where('product_stock_id', $record->id)
                                ->latest()
                                ->limit(50)
                                ->get();
                        });
                        return  view('filament.pages.product.view-history-product-stock')
                            ->with(['record' => $record, 'product' =>  $product, 'histories' =>  $histories]);
                    })
                    ->modalSubmitAction(false)
                    ->closeModalByClickingAway(false),
                // ->url(fn($record) => static::getUrl('view-history', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistoryProductStocks::route('/'),
            // 'view-history' => Pages\ViewHistoryProductStock::route('/{record}/history'),
            // 'create' => Pages\CreateHistoryProductStock::route('/create'),
            // 'edit' => Pages\EditHistoryProductStock::route('/{record}/edit'),
        ];
    }
}
