<?php

namespace App\Filament\Resources\Pembelian;

use App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;
use App\Models\Mitra\Supplier;
use App\Models\Pembelian\PenerimaanBarang;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 2;
    protected static ?string $model = PenerimaanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?int $navigationSort = 7;
    public $products = [];

    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        return static::checkMenuAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkMenuAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMenuAccess('can_delete', static::$menuId);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('transaction_type')
                    ->default('penerimaan_barang'),
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
                Forms\Components\TextInput::make('penerimaan_barang_invoicenumber')
                    ->label('Nomor Invoice')
                    ->unique(
                        table: 'penerimaan_barangs',
                        column: 'penerimaan_barang_invoicenumber',
                        ignoreRecord: true,
                        modifyRuleUsing: fn($rule, callable $get) =>
                        $rule->where('outlet_id', $get('outlet_id'))
                    )
                    // ->required()
                    ->readOnly(fn($livewire) => $livewire->isValidated ?? false),
                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('supplier_name', 'id'))
                    ->searchable()
                    // ->required()
                    ->disableOptionWhen(fn($livewire) => $livewire->isValidated ?? false),
                Forms\Components\DatePicker::make('penerimaan_barang_tanggal')
                    ->date()
                    ->label('Tanggal')
                    ->default(NOW())
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->closeOnDateSelection(true)
                    ->required()
                    ->readOnly(fn($livewire) => $livewire->isValidated ?? false),

                // baris kedua full width
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->readOnly(fn($livewire) => $livewire->isValidated ?? false)
                    ->columnSpanFull(),
                Forms\Components\ViewField::make('selected_products')
                    ->label('Produk Terpilih')
                    ->view('filament.forms.selected-products', ['menu_id' => 2])
                    ->columnSpanFull()
                    ->dehydrated(false),

                Forms\Components\Hidden::make('penerimaan_barang_total')
                    ->label('Total')
                    ->default(0),
                Forms\Components\Hidden::make('penerimaan_barang_discount')
                    ->label('Disc')
                    ->default(0),
                Forms\Components\Hidden::make('penerimaan_barang_discounttype')
                    ->label('Disc type')
                    ->default('percent'),
                Forms\Components\Hidden::make('penerimaan_barang_grandtotal')
                    ->label('Grand Total')
                    ->default(0),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        // dd(static::user()->getCachedMenuAccess(2));
        return $table
            ->header(function () {
                return view('filament.tables.date-range-filter');
            })
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('penerimaan_barang_tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penerimaan_barang_invoicenumber')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan'),
                //
            ])
            ->defaultSort('penerimaan_barang_tanggal', 'desc')
            ->searchable(false)
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->penerimaan_barang_status === 'pending'),
                Tables\Actions\Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->url(fn($record) => static::getUrl('validate', ['record' => $record]))
                    ->visible(fn($record) =>
                    $record->penerimaan_barang_status === 'pending'
                        && static::user()->getCachedMenuAccess(2)->can_validate),
                Tables\Actions\Action::make('validatedLabel')
                    ->label('Validated')
                    ->color('success')
                    ->disabled() // biar jadi text, tidak bisa diklik
                    ->visible(fn($record) =>  $record->penerimaan_barang_status === 'validated'),
                Tables\Actions\Action::make('unvalidate')
                    ->label('UnValidated')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn($record) => $record->penerimaan_barang_status === 'validated')
                    ->requiresConfirmation() // munculkan modal konfirmasi
                    ->modalHeading('Konfirmasi Unvalidasi')
                    ->modalSubheading('Apakah Anda yakin ingin membatalkan validasi data ini?')
                    ->modalButton('Ya, Unvalidasi')
                    ->action(function ($record) {
                        static::unvalidasi($record);
                    }),
                // Tables\Actions\Action::make('print')
                //     ->label('Cetak')
                //     ->icon('heroicon-o-printer')
                //     ->color('primary')
                //     ->url(fn($record) => route('penerimaan-barang.cetak', ['penerimaan_barang' => $record->id]))
                //     ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) =>  $record->penerimaan_barang_status === 'pending'),
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
            'index' => Pages\ListPenerimaanBarangs::route('/'),
            'create' => Pages\CreatePenerimaanBarang::route('/create'),
            'edit' => Pages\EditPenerimaanBarang::route('/{record}/edit'),
            'validate' => Pages\ValidatePenerimaanBarang::route('/{record}/validate'),
            'unvalidate' => Pages\UnValidatePenerimaanBarang::route('/{record}/unvalidate'),
        ];
    }

    public static function unvalidasi($record): void
    {
        DB::transaction(function () use ($record) {

            $record->update([
                'penerimaan_barang_status' => 'pending',
            ]);
            foreach ($record->penerimaanBarangDetail as $detail) {
                $productStock = ProductStock::where('product_id', $detail['product_id'])
                    ->where('product_stock_sn', $detail['penerimaan_barang_detail_sn'])
                    ->where('product_stock_ed', $detail['penerimaan_barang_detail_ed'])
                    ->first();
                if ($productStock) {
                    $stockAwal = $productStock['stock'];
                    $stockAkhir =  $stockAwal - $detail['penerimaan_barang_detail_qty'];

                    $totalHargaBeli = $stockAkhir * $detail['penerimaan_barang_detail_price'];
                    $productStock->update([
                        'stock' =>  $stockAkhir
                    ]);


                    ProductStockHistories::where('no_transaksi', $record['penerimaan_barang_invoicenumber'])->delete();
                }
            }
        });

        Notification::make()
            ->title('Unvalidasi Success')
            ->success()
            ->send();
    }
}
