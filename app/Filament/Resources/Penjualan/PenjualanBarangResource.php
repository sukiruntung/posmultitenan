<?php

namespace App\Filament\Resources\Penjualan;

use App\Filament\Resources\Penjualan\PenjualanBarangResource\Pages;
use App\Models\Penjualan\PenjualanBarang;
use App\Models\Mitra\Customer;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use App\Services\ThermalPrinterService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Support\Assets\Js;
use App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs;
use App\Traits\CheckPermissionAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PenjualanBarangResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 3;
    protected static ?string $model = PenjualanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Penjualan';

    protected static ?string $pluralModelLabel = 'Surat Jalan / Faktur';
    protected static ?string $navigationLabel = 'Surat Jalan / Faktur';
    protected static ?int $navigationSort = 13;
    protected ThermalPrinterService $thermalPrinterService;
    public $products = [];
    public function __construct()
    {
        $this->thermalPrinterService = app(ThermalPrinterService::class);
    }
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
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Hidden::make('transaction_type')
                            ->default('penjualan_barang'),
                        Forms\Components\Hidden::make('outlet_id')
                            ->default(Auth::user()->userOutlet->outlet_id),
                        Forms\Components\TextInput::make('penjualan_barang_no')
                            ->label('Nomor Surat Jalan')
                            ->readOnly()
                            ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('customer_name', 'id'))
                            ->searchable()
                            // ->required()
                            ->disableOptionWhen(fn($livewire) => $livewire->isValidated ?? false)
                            ->live()
                            ->afterStateUpdated(function ($state, $livewire) {
                                if ($livewire instanceof \Filament\Resources\Pages\CreateRecord || $livewire instanceof \Filament\Resources\Pages\EditRecord) {
                                    $livewire->dispatch('customer-changed', $state);
                                }
                            }),
                        Forms\Components\DatePicker::make('penjualan_barang_tanggal')
                            ->date()
                            ->label('Tanggal')
                            ->default(NOW())
                            ->format('Y-m-d')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection(true)
                            ->required()
                            ->readOnly(fn($livewire) => $livewire->isValidated ?? false),
                        Forms\Components\DatePicker::make('penjualan_barang_tanggaljth')
                            ->date()
                            ->label('Tanggal Jatuh Tempo')
                            ->default(
                                NOW()
                                    ->addMonths(1)
                            )
                            ->format('Y-m-d')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection(true)
                            ->required()
                            ->readOnly(fn($livewire) => $livewire->isValidated ?? false),
                    ]),



                // baris kedua full width
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->readOnly(fn($livewire) => $livewire->isValidated ?? false)
                    ->columnSpanFull(),
                Forms\Components\ViewField::make('selected_products')
                    ->label('Produk Terpilih')
                    ->view('filament.forms.selected-penjualan-product')
                    ->columnSpanFull()
                    ->dehydrated(false),

                Forms\Components\Hidden::make('penjualan_barang_total')
                    ->label('Total')
                    ->default(0),
                Forms\Components\Hidden::make('penjualan_barang_discount')
                    ->label('Disc')
                    ->default(0),
                Forms\Components\Hidden::make('penjualan_barang_discounttype')
                    ->label('Disc type')
                    ->default('percent'),
                Forms\Components\Hidden::make('penjualan_barang_ppn')
                    ->label('PPN Rate (%)')->default(function () {
                        // ambil nilai default dari menu access
                        $menuAccess = static::user()->getCachedMenuAccess(3);
                        return $menuAccess->can_ppn ? $menuAccess->ppn_rate : 0;
                    }),

                Forms\Components\Hidden::make('penjualan_barang_ongkir')
                    ->label('Ongkos Kirim')
                    ->default(0),

                Forms\Components\Hidden::make('penjualan_barang_grandtotal')
                    ->label('Grand Total')
                    ->default(0),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->header(function () {

                return view('filament.tables.date-range-filter');
            })
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('penjualan_barang_tanggal')
                    ->label("Tanggal")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penjualan_barang_no')
                    ->label('Nomor Surat Jalan')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('customer.customer_name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan'),
            ])

            ->defaultSort('penjualan_barang_tanggal', 'desc')
            ->filters([
                //
            ])
            ->searchable(false)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->penjualan_barang_status === 'pending'),
                Tables\Actions\Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->url(fn($record) => static::getUrl('validate', ['record' => $record]))
                    ->visible(fn($record) =>
                    $record->penjualan_barang_status === 'pending'
                        && static::user()->getCachedMenuAccess(3)->can_validate),

                Tables\Actions\Action::make('unvalidate')
                    ->label('UnValidated')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn($record) => $record->penjualan_barang_status === 'validated')
                    ->requiresConfirmation() // munculkan modal konfirmasi
                    ->modalHeading('Konfirmasi Unvalidasi')
                    ->modalSubheading('Apakah Anda yakin ingin membatalkan validasi data ini?')
                    ->modalButton('Ya, Unvalidasi')
                    ->action(function ($record, array $data) {
                        static::unvalidasi($record, $data);
                    }),

                Tables\Actions\Action::make('printsj')
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalHeading('Print Preview')
                    ->modalSubmitAction(false)
                    ->modalContent(function ($record) {
                        $username = static::user()->name;
                        $host = request()->getScheme() . '://' . request()->getHost();
                        $port = config('app.report_port');
                        if (!empty($port)) {
                            $host .= ":{$port}";
                        }

                        $url = "{$host}/rpt/?r=postexample/rpt_penjualanbarang&d=postexample&p=PID&v={$record->id}&t=s|s&u={$username}&f=pdf&tm=" . now()->format('YmdHis');

                        return view('filament.layouts.modals.report', compact('url'));
                    })
                    ->visible(fn($record) =>
                    in_array($record->penjualan_barang_status, ['validated', 'belum lunas', 'lunas'])
                        && static::user()->getCachedMenuAccess(3)->can_print2),

                Tables\Actions\Action::make('printthermal')
                    ->label('Cetak Thermal')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->action(function ($record, $livewire) {
                        $ESC = "\x1B";

                        // Header dengan font normal
                        $text  = $ESC . "@";           // reset
                        $text .= $ESC . "a" . "1";     // center align
                        $text .= "\n";
                        $text .= "================================\n";
                        $text .= "        PENJUALAN BARANG        \n";
                        $text .= "================================\n";
                        $text .= $ESC . "a" . "0";     // left align
                        $text .= "No: {$record->penjualan_barang_no}\n";
                        $text .= "Tgl: {$record->penjualan_barang_tanggal}\n";
                        $text .= "Customer: {$record->customer->customer_name}\n";
                        $text .= "--------------------------------\n";

                        // Detail produk dengan font kecil
                        $text .= $ESC . "M" . "1";     // small font
                        foreach ($record->penjualanBarangDetail as $detail) {
                            $text .= "{$detail->penjualan_barang_detailproduct_name}\n";
                            $text .= "  {$detail->penjualan_barang_detail_qty} x " . number_format($detail->penjualan_barang_detail_price);
                            $text .= " = " . number_format($detail->penjualan_barang_detail_total) . "\n";
                        }

                        // Footer dengan font normal
                        $text .= $ESC . "M" . "0";     // normal font
                        $text .= "--------------------------------\n";
                        $text .= "Total: Rp " . number_format($record->penjualan_barang_grandtotal) . "\n";
                        $text .= "================================\n";
                        $text .= $ESC . "a" . "1";     // center align
                        $text .= "     Terima Kasih\n";
                        $text .= "\n\n";

                        $livewire->dispatch('printer-print', $text);
                    }),

                Tables\Actions\Action::make('printfaktur')
                    ->label('Cetak Faktur')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->modalWidth('7xl')
                    ->modalHeading('Print Preview')
                    ->modalSubmitAction(false)
                    ->modalContent(function ($record) {
                        $username = static::user()->name;
                        $host = request()->getScheme() . '://' . request()->getHost();
                        $port = config('app.report_port');
                        if (!empty($port)) {
                            $host .= ":{$port}";
                        }

                        $url = "{$host}/rpt/?r=postexample/rpt_fakturbarang&d=postexample&p=PID&v={$record->id}&t=s|s&u={$username}&f=pdf&tm=" . now()->format('YmdHis');

                        return view('filament.layouts.modals.report', compact('url'));
                    })
                    ->visible(fn($record) => in_array($record->penjualan_barang_status, ['validated', 'belum lunas', 'lunas'])
                        && static::user()->getCachedMenuAccess(3)->can_print2),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->penjualan_barang_status === 'pending'),
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

    public static function getWidgets(): array
    {
        return [
            PaymentTabs::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualanBarangs::route('/'),
            'create' => Pages\CreatePenjualanBarang::route('/create'),
            'edit' => Pages\EditPenjualanBarang::route('/{record}/edit'),
            'validate' => Pages\ValidatePenjualanBarang::route('/{record}/validate'),

        ];
    }

    public static function unvalidasi($record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {

            $record->update([
                'penjualan_barang_status'   => 'pending',
            ]);
            foreach ($record->penjualanBarangDetail as $detail) {
                $productStock = ProductStock::where('id', $detail['product_stock_id'])
                    ->first();
                if ($productStock) {
                    $stockAwal = $productStock['stock'];
                    $stockAkhir =  $stockAwal + $detail['penjualan_barang_detail_qty'];

                    $harga_jual = $detail['penjualan_barang_detail_price'];
                    $productStock->update([
                        'stock' =>  $stockAkhir
                    ]);

                    ProductStockHistories::where('no_transaksi', $record['penjualan_barang_no'])->delete();
                }
            }
        });

        Notification::make()
            ->title('Unvalidasi Success')
            ->success()
            ->send();
    }
    // public function getFooterScripts(): array
    // {
    //     return [
    //         Js::make('thermal-printer', asset('js/thermal-printer.js')),
    //     ];
    // }
    // public function getHeaderScripts(): array
    // {
    //     return [
    //         Js::make('thermal-printer', asset('js/thermal-printer.js')),
    //     ];
    // }
}
