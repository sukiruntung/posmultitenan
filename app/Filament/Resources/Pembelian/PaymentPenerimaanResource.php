<?php

namespace App\Filament\Resources\Pembelian;

use App\Filament\Resources\Pembelian\PaymentPenerimaanResource\Pages;
use App\Models\Accounting\KasHarian;
use App\Models\Accounting\KasPemasukkan;
use App\Models\Pembelian\PaymentPenerimaan;
use App\Models\Pembelian\PenerimaanBarang;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentPenerimaanResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 9;
    protected static ?string $model = PenerimaanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    // protected static ?string $modelLabel = 'Payment Penjualan';
    protected static ?string $pluralModelLabel = 'Payment Supplier';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?string $navigationLabel = 'Payment Supplier';
    protected static ?int $navigationSort = 8;

    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    // public static function canCreate(): bool
    // {
    //     return static::checkMenuAccess('can_create', static::$menuId);
    // }

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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->header(function () {

                return view('filament.tables.date-range-filter');
            })
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                    ->whereIn('penerimaan_barang_status', ['validated', 'belum lunas', 'lunas'])
            )

            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('penerimaan_barang_tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penerimaan_barang_invoicenumber')
                    ->label('Nomor Surat Jalan')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('penerimaan_barang_grandtotal')
                    ->label('Grand Total')
                    ->money('idr', true),
                Tables\Columns\TextColumn::make('penerimaan_barang_jumlahpayment')
                    ->label('Nominal Bayar')
                    ->money('idr', true),
            ])
            ->filters([
                //
            ])
            ->searchable(false)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label("Bayar")
                    ->icon('heroicon-o-currency-dollar')
                    ->modalHeading('Payment Supplier')
                    ->form(fn($record) =>
                    [
                        // dd($record->toArray()),
                        Forms\Components\TextInput::make('jumlah_bayar')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->prefix('Rp')
                            ->afterStateHydrated(function ($set, $record) {
                                return $set('jumlah_bayar', number_format($record->penerimaan_barang_grandtotal - $record->penerimaan_barang_jumlahpayment, 0, ',', '.'));
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('payment_penerimaan_grandtotal')
                            ->label('Grand Total')
                            ->prefix('Rp')
                            ->afterStateHydrated(fn($set, $record) => $set('payment_penerimaan_grandtotal', number_format($record->penerimaan_barang_grandtotal - $record->penerimaan_barang_jumlahpayment, 0, ',', '.')))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('payment_penerimaan_metode')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                                'edc' => 'EDC',
                                'qris' => 'QRIS',
                                'ewallet' => 'E-Wallet',
                                'giro' => 'Giro/Cek',
                            ])
                            ->afterStateHydrated(fn($set, $state) => $set('payment_penerimaan_metode', $state ?: 'cash'))
                            ->reactive()
                            ->required(),
                        // Transfer
                        Forms\Components\TextInput::make('payment_penerimaan_bankname')
                            ->label('Bank Name')
                            ->visible(fn($get) => $get('payment_penerimaan_metode') === 'transfer' || $get('payment_penerimaan_metode') === 'edc'),

                        Forms\Components\TextInput::make('payment_penerimaan_accountnumber')
                            ->label('Account Number')
                            ->visible(fn($get) => $get('payment_penerimaan_metode') === 'transfer'),

                        // EDC
                        Forms\Components\TextInput::make('payment_penerimaan_approvalcode')
                            ->label('Approval Code')
                            ->visible(fn($get) => $get('payment_penerimaan_metode') === 'edc'),

                        // QRIS & E-Wallet
                        Forms\Components\TextInput::make('payment_penerimaan_referenceid')
                            ->label('Reference ID')
                            ->visible(fn($get) => in_array($get('payment_penerimaan_metode'), ['qris', 'ewallet'])),

                        // Giro / Cek
                        Forms\Components\TextInput::make('payment_penerimaan_checkquenumber')
                            ->label('Cheque Number')
                            ->visible(fn($get) => $get('payment_penerimaan_metode') === 'giro'),

                        Forms\Components\DatePicker::make('payment_penerimaan_jatuhtempo')
                            ->label('Jatuh Tempo')
                            ->visible(fn($get) => $get('payment_penerimaan_metode') === 'giro'),
                    ])
                    ->action(function ($record, array $data) {
                        $data['jumlah_bayar'] = (int) str_replace('.', '', trim($data['jumlah_bayar']));
                        DB::transaction(function () use ($record, $data) {
                            if (Auth::user()->is_kasir == false) {
                                Notification::make()
                                    ->title('Akses Ditolak')
                                    ->body('Akun anda tidak berhak melakukan transaksi ini')
                                    ->danger()
                                    ->send();

                                return null;
                            }
                            $grandTotal = (int) $record->penerimaan_barang_grandtotal;
                            $totalBayar = (int) $record->penerimaan_barang_jumlahpayment;
                            $bayarBaru = (int) $data['jumlah_bayar'];

                            $jumlahBayarAkhir = $totalBayar + $bayarBaru;
                            $status = $jumlahBayarAkhir >= $grandTotal ? 'lunas' : 'belum lunas';

                            // 🔹 update penerimaan
                            $record->update([
                                'penerimaan_barang_status' => $status,
                                'penerimaan_barang_jumlahpayment' => $jumlahBayarAkhir,
                            ]);

                            // 🔹 create payment
                            $paymentPenerimaan = PaymentPenerimaan::create([
                                'penerimaan_barang_id' => $record->id,
                                'payment_penerimaan_tanggal' => now(),
                                'payment_penerimaan_metode' => $data['payment_penerimaan_metode'],
                                'payment_penerimaan_status' => $status,
                                'payment_penerimaan_jumlah' => $bayarBaru,
                                'payment_penerimaan_grandtotal' => $grandTotal,
                                'payment_penerimaan_bankname' => $data['payment_penerimaan_bankname'] ?? null,
                                'payment_penerimaan_accountnumber' => $data['payment_penerimaan_accountnumber'] ?? null,
                                'payment_penerimaan_approvalcode' => $data['payment_penerimaan_approvalcode'] ?? null,
                                'payment_penerimaan_referenceid' => $data['payment_penerimaan_referenceid'] ?? null,
                                'payment_penerimaan_checkquenumber' => $data['payment_penerimaan_checkquenumber'] ?? null,
                                'payment_penerimaan_jatuhtempo' => $data['payment_penerimaan_jatuhtempo'] ?? null,
                                'user_id' => Auth::id(),
                            ]);
                            $kas = KasHarian::where('kasir_id', Auth::id())
                                ->where('outlet_id', Auth::user()->userOutlet->outlet_id)
                                ->whereDate('kas_harian_tanggalbuka', today())
                                ->first();

                            if (!$kas) {
                                $kas = KasHarian::create([
                                    'outlet_id' => Auth::user()->userOutlet->outlet_id,
                                    'kasir_id' => Auth::id(),
                                    'kas_harian_tanggalbuka' => now(),
                                    'kas_harian_tanggaltutup' => null,
                                    'kas_harian_status' => 'buka',
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            KasPemasukkan::create([
                                'kas_harian_id' =>  $kas->id, // <— ambil ID kas hari ini
                                'kasir_id' => Auth::id(),
                                'kas_pemasukkan_jenis' => 'keluar',
                                'kas_pemasukkan_jumlah' => $bayarBaru,
                                'kas_pemasukkan_sumber' => 'PaymentPenerimaan',
                                'kas_pemasukkan_reference' => $paymentPenerimaan->id,
                                'kas_pemasukkan_notransaksi' => $record->penerimaan_barang_invoicenumber,
                                'kas_pemasukkan_notes' => $data['payment_penerimaan_metode'],
                                'user_id' => Auth::id(),
                            ]);
                        });
                    })
                    ->visible(
                        fn($record) => in_array($record->penerimaan_barang_status, ['validated', 'belum lunas'])
                    ),
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-information-circle')
                    ->button()
                    ->color('success')
                    ->modalHeading('Detail Payment Supplier')
                    ->modalWidth('2xl')
                    ->form(fn($record) =>
                    [
                        Forms\Components\ViewField::make('detail_payment')
                            ->view('filament.tables.table-payment-penerimaan', [
                                'record' => $record,
                            ]),
                    ])
                    ->modalSubmitAction(false)
                    ->visible(fn($record) => in_array($record->penerimaan_barang_status, ['belum lunas', 'lunas']))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
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
            'index' => Pages\ListPaymentPenerimaans::route('/'),
            // 'create' => Pages\CreatePaymentPenerimaan::route('/create'),
            // 'edit' => Pages\EditPaymentPenerimaan::route('/{record}/edit'),
        ];
    }
}
