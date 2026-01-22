<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\ProductStockResource\Pages;
use App\Filament\Resources\Products\ProductStockResource\RelationManagers;
use App\Models\Products\Product;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class ProductStockResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 11;
    protected static ?string $model = ProductStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationLabel = 'Stock Products';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Select::make('product_id')
                    ->options(Product::with(['satuan', 'merk'])
                        ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                        ->get()
                        ->mapWithKeys(function ($product) {
                            return [
                                $product->id => $product->product_name
                                    . ' ( ' . ($product->merk->merk_name ?? '')   . ' ) '
                                    . ' - ' . ($product->satuan->satuan_name ?? '')
                            ];
                        }))
                    ->label('Nama Produk')
                    ->required(),
                Forms\Components\TextInput::make('product_stock_sn')
                    ->label('Serial Number')
                    ->nullable()
                    ->maxLength(50)
                    ->unique(
                        table: ProductStock::class,
                        column: 'product_stock_sn',
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule, callable $get) =>
                        $rule->where('product_id', $get('product_id'))
                    ),
                Forms\Components\DatePicker::make('product_stock_ed')
                    ->date()
                    ->format('Y-m-d')
                    ->label('Expired Date')
                    ->displayFormat('d/m/Y') // format tampilan di form
                    ->native(false)
                    ->closeOnDateSelection(true)
                    ->nullable(),

                Forms\Components\TextInput::make('stock')
                    ->label('Stock')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('product', fn($q) => $q->where('outlet_id', Auth::user()->userOutlet->outlet_id)))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('product.product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.satuan.satuan_name')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('product.merk.merk_name')
                    ->label('Merk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_stock_sn')
                    ->label('Serial Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_stock_ed')
                    ->label('ED')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(Product::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('product_name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->using(function (ProductStock $productStock, array $data) {
                        return DB::transaction(function () use ($productStock, $data) {
                            $stockAwal  = $productStock->getOriginal('stock'); // stok lama
                            $stockAkhir = $data['stock'];
                            // update record utama (ProductStock)
                            $data['user_id'] = Auth::id();
                            $productStock->update($data);

                            // hanya buat history jika stock berubah
                            if ($stockAkhir != $stockAwal) {
                                if ($stockAkhir > $stockAwal) {
                                    // ada penambahan stok
                                    $qtyMasuk = $stockAkhir - $stockAwal;
                                    $qtyKeluar = 0;
                                } elseif ($stockAkhir < $stockAwal) {
                                    $qtyMasuk = 0;
                                    $qtyKeluar = $stockAwal - $stockAkhir;
                                } else {
                                    // tidak ada perubahan stok
                                    $qtyMasuk = 0;
                                    $qtyKeluar = 0;
                                }
                                // simpan history
                                ProductStockHistories::create([
                                    'product_stock_id' => $productStock->id,
                                    'qty_masuk' => $qtyMasuk,
                                    'qty_keluar' => $qtyKeluar,
                                    'stock_awal' => $stockAwal,             // stok baru
                                    'stock_akhir' => $data['stock'],
                                    // 'harga_beli' => $data['harga_beli'],
                                    // 'total_harga_beli' => $data['harga_beli'] * $data['stock'],
                                    'jenis' => 'pemulihan stock',
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            return $productStock;
                        });
                    }),
                // Tables\Actions\DeleteAction::make()
                //     ->action(function (ProductStock $record) {
                //         $record->user_id = Auth::id();
                //         $record->save();
                //         $record->delete();
                //     }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProductStocks::route('/'),
        ];
    }
}
