<?php

namespace App\Filament\Resources\Penjualan;

use App\Filament\Resources\Penjualan\PaymentPenjualanResource\Pages;
use App\Models\Penjualan\PaymentPenjualan;
use App\Models\Penjualan\PenjualanBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs;
use App\Models\Accounting\KasHarian;
use App\Models\Accounting\KasPemasukkan;
use App\Traits\CheckPermissionAccess;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentPenjualanResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 4;
    protected static ?string $model = PenjualanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    // protected static ?string $modelLabel = 'Payment Penjualan';
    protected static ?string $pluralModelLabel = 'Payment Penjualan';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $navigationLabel = 'Payment';
    protected static ?int $navigationSort = 14;

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
            ->schema([]);
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
                    ->whereIn('penjualan_barang_status', ['validated', 'belum lunas', 'lunas'])
            )
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('penjualan_barang_tanggal')
                    ->label('Tanggal')
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
                Tables\Columns\TextColumn::make('penjualan_barang_grandtotal')
                    ->label('Grand Total')
                    ->money('idr', true),
                Tables\Columns\TextColumn::make('penjualan_barang_jumlahpayment')
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
                    ->modalHeading('Payment Penjualan')
                    ->form(fn($record) =>
                    [
                        // dd($record->toArray()),
                        Forms\Components\TextInput::make('jumlah_bayar')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->prefix('Rp')
                            ->afterStateHydrated(function ($set, $record) {
                                return $set('jumlah_bayar', number_format($record->penjualan_barang_grandtotal - $record->penjualan_barang_jumlahpayment, 0, ',', '.'));
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('payment_penjualan_grandtotal')
                            ->label('Grand Total')
                            ->prefix('Rp')
                            ->afterStateHydrated(fn($set, $record) => $set('payment_penjualan_grandtotal', number_format($record->penjualan_barang_grandtotal - $record->penjualan_barang_jumlahpayment, 0, ',', '.')))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('payment_penjualan_metode')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                                'edc' => 'EDC',
                                'qris' => 'QRIS',
                                'ewallet' => 'E-Wallet',
                                'giro' => 'Giro/Cek',
                            ])
                            ->afterStateHydrated(fn($set, $state) => $set('payment_penjualan_metode', $state ?: 'cash'))
                            ->reactive()
                            ->required(),
                        // Transfer
                        Forms\Components\TextInput::make('payment_penjualan_bankname')
                            ->label('Bank Name')
                            ->visible(fn($get) => $get('payment_penjualan_metode') === 'transfer' || $get('payment_penjualan_metode') === 'edc'),

                        Forms\Components\TextInput::make('payment_penjualan_accountnumber')
                            ->label('Account Number')
                            ->visible(fn($get) => $get('payment_penjualan_metode') === 'transfer'),

                        // EDC
                        Forms\Components\TextInput::make('payment_penjualan_approvalcode')
                            ->label('Approval Code')
                            ->visible(fn($get) => $get('payment_penjualan_metode') === 'edc'),

                        // QRIS & E-Wallet
                        Forms\Components\TextInput::make('payment_penjualan_referenceid')
                            ->label('Reference ID')
                            ->visible(fn($get) => in_array($get('payment_penjualan_metode'), ['qris', 'ewallet'])),

                        // Giro / Cek
                        Forms\Components\TextInput::make('payment_penjualan_checkquenumber')
                            ->label('Cheque Number')
                            ->visible(fn($get) => $get('payment_penjualan_metode') === 'giro'),

                        Forms\Components\DatePicker::make('payment_penjualan_jatuhtempo')
                            ->label('Jatuh Tempo')
                            ->visible(fn($get) => $get('payment_penjualan_metode') === 'giro'),
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
                            $grandTotal = (int) $record->penjualan_barang_grandtotal;
                            $totalBayar = (int) $record->penjualan_barang_jumlahpayment;
                            $bayarBaru = (int) $data['jumlah_bayar'];
                            $jumlahBayarAkhir = $totalBayar + $bayarBaru;
                            $status = $jumlahBayarAkhir >= $grandTotal ? 'lunas' : 'belum lunas';
                            $record->update([
                                'penjualan_barang_status' => $status,
                                'penjualan_barang_jumlahpayment' => $jumlahBayarAkhir,
                            ]);

                            // 🔹 create payment
                            $paymentPenjualan = PaymentPenjualan::create([
                                'penjualan_barang_id' => $record->id,
                                'payment_penjualan_tanggal' => now(),
                                'payment_penjualan_metode' => $data['payment_penjualan_metode'],
                                'payment_penjualan_status' => $status,
                                'payment_penjualan_jumlah' => $bayarBaru,
                                'payment_penjualan_grandtotal' => $grandTotal,
                                'payment_penjualan_bankname' => $data['payment_penjualan_bankname'] ?? null,
                                'payment_penjualan_accountnumber' => $data['payment_penjualan_accountnumber'] ?? null,
                                'payment_penjualan_approvalcode' => $data['payment_penjualan_approvalcode'] ?? null,
                                'payment_penjualan_referenceid' => $data['payment_penjualan_referenceid'] ?? null,
                                'payment_penjualan_checkquenumber' => $data['payment_penjualan_checkquenumber'] ?? null,
                                'payment_penjualan_jatuhtempo' => $data['payment_penjualan_jatuhtempo'] ?? null,
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
                                    'kas_harian_status' =>  'buka',
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            KasPemasukkan::create([
                                'kas_harian_id' =>  $kas->id, // <— ambil ID kas hari ini
                                'kasir_id' => Auth::id(),
                                'kas_pemasukkan_jenis' => 'masuk',
                                'kas_pemasukkan_jumlah' => $bayarBaru,
                                'kas_pemasukkan_sumber' => 'PaymentPenjualan',
                                'kas_pemasukkan_reference' => $paymentPenjualan->id,
                                'kas_pemasukkan_notransaksi' => $record->penjualan_barang_no,
                                'kas_pemasukkan_notes' => $data['payment_penjualan_metode'],
                                'user_id' => Auth::id(),
                            ]);
                        });
                    })
                    ->visible(
                        fn($record) => in_array($record->penjualan_barang_status, ['validated', 'belum lunas'])
                    ),
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-information-circle')
                    ->button()
                    ->color('success')
                    ->modalHeading('Detail Payment')
                    ->modalWidth('2xl')
                    ->form(fn($record) =>
                    [
                        Forms\Components\ViewField::make('detail_payment')
                            ->view('filament.tables.table-payment', [
                                'record' => $record,
                            ]),
                    ])
                    ->modalSubmitAction(false)
                    ->visible(fn($record) => in_array($record->penjualan_barang_status, ['belum lunas', 'lunas']))
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

    public static function getWidgets(): array
    {
        return [
            PaymentTabs::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentPenjualans::route('/'),
            // 'create' => Pages\CreatePaymentPenjualan::route('/create'),
            // 'edit' => Pages\EditPaymentPenjualan::route('/{record}/edit'),
        ];
    }
}
