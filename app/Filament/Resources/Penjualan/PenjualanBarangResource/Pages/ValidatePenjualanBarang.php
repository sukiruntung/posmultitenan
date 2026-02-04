<?php

namespace App\Filament\Resources\Penjualan\PenjualanBarangResource\Pages;

use App\Filament\Resources\Penjualan\PenjualanBarangResource;
use App\Jobs\SendWaNotif;
use App\Models\Mitra\CustomerProduct;
use App\Models\Penjualan\PenjualanBarangDetail;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ValidatePenjualanBarang extends EditRecord
{
    protected static string $resource = PenjualanBarangResource::class;
    public $products = [];
    public bool $isValidated = true;
    // protected static string $view = 'filament.resources.penjualan-barang.pages.validate'; // optional, kalau mau ganti blade

    public function getTitle(): string
    {
        return 'Validasi Penjualan Barang';
    }
    public function mount($record): void
    {
        parent::mount($record);
        $this->products = PenjualanBarangDetail::with('productStock', 'product', 'product.satuan', 'product.merk')
            ->where('penjualan_barang_id', $record)
            ->get()
            ->map(fn($detail) => [
                'id'         => $detail->product_stock_id,
                'product_id' => $detail->product_id,
                'name'       => $detail->penjualan_barang_detailproduct_name,
                'merk'       => $detail->product->merk->merk_name ?? '',
                'satuan_id'  => $detail->product->satuan_id,
                'satuan_name' => $detail->product->satuan->satuan_name ?? '',
                'sn'         => $detail->penjualan_barang_detail_sn,
                'ed'         => $detail->penjualan_barang_detail_ed,
                'qty'        => $detail->penjualan_barang_detail_qty,
                'errorQty'   => false,
                'harga'      => array_unique([
                    $detail->penjualan_barang_detail_price,
                    $detail->product->hargajualgrosir,
                    $detail->product->hargajual1,
                    $detail->product->hargajual2,
                    $detail->product->hargajual3,
                ]),
                'harga_default' => $detail->penjualan_barang_detail_price,
                'harga_mode' => 'default',
                'disc'       => $detail->penjualan_barang_detail_discount,
                'disc_type'  => $detail->penjualan_barang_detail_discounttype,
                'subtotal'   => $detail->penjualan_barang_detail_total,
            ])
            ->toArray();


        $this->form->fill([
            ...$this->form->getState(),
            'transaction_type' => 'penjualan_barang',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('validate')
                ->label('Validasi')
                ->color('success')
                ->action(function () {
                    $data = $this->form->getState();
                    $this->validasi($data);
                }),
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')), // trigger method validate()
        ];
    }
    public function validasi(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($this->products as $detail) {
                $productStock = ProductStock::find($detail['id']);

                if (!$productStock) {
                    Notification::make()
                        ->title('Gagal menyimpan data')
                        ->body("Data produk {$detail['name']} tidak ditemukan.")
                        ->danger()
                        ->send();

                    return null;
                }

                $stockAwal  = $productStock->stock;
                $stockAkhir = $stockAwal - $detail['qty'];

                if ($stockAkhir < 0) {
                    Notification::make()
                        ->title('Gagal menyimpan data')
                        ->body("Stock untuk produk {$detail['name']} tidak mencukupi.")
                        ->danger()
                        ->send();

                    return null;
                }
                $productStock->update([
                    'stock' => $stockAkhir,
                ]);

                $cekHistory = ProductStockHistories::where([
                    ['product_stock_id', '=', $productStock->id],
                    ['no_transaksi', '=', $data['penjualan_barang_no']],
                ])->exists();

                if ($cekHistory) {
                    Notification::make()
                        ->title('Gagal menyimpan data')
                        ->body("Data history untuk produk {$detail['name']} sudah ada.")
                        ->danger()
                        ->send();

                    return null;
                }

                // Simpan history
                ProductStockHistories::create([
                    'tanggal'        => now(),
                    'product_stock_id' => $productStock->id,
                    'qty_keluar'     => $detail['qty'],
                    'stock_awal'     => $stockAwal,
                    'stock_akhir'    => $stockAkhir,
                    'harga_jual'     => $detail['harga_default'],
                    'jenis'          => 'barang keluar',
                    'keterangan'     => $data['notes'],
                    'no_transaksi'   => $data['penjualan_barang_no'],
                    'user_id'        => Auth::id(),
                ]);
                $customerProduct = CustomerProduct::updateOrCreate(
                    [
                        'customer_id' => $this->record->customer_id,
                        'product_id'  => $productStock->product_id,
                    ],
                    [
                        'first_product_stock_id' => $detail['id'],
                        'harga_jual'             => $detail['harga_default'],
                    ]
                );
                $customerProduct->increment('frekuensi');
            }
            $this->record->update([
                'penjualan_barang_validatedby' => Auth::id(),
                'penjualan_barang_validatedat' => now(),
                'penjualan_barang_status'      => 'validated',
            ]);
            DB::afterCommit(function () use ($data) {
                $msg =
                    "Penjualan berhasil dibuat\n" .
                    "No: {$data['penjualan_barang_no']}\n" .
                    "Customer: {$this->record->customer->customer_name}\n" .
                    "Tanggal: " . now()->format('d-m-Y H:i') . "\n" .
                    "Terima kasih telah berbelanja di toko kami.\n";
                $this->record->loadMissing('outlet.owner');
                $ownerPhone = $this->record->outlet?->owner?->phone;

                if (!$ownerPhone) {
                    return;
                }

                $ownerPhone = preg_replace('/\D+/', '', $ownerPhone);

                if (str_starts_with($ownerPhone, '0')) {
                    $ownerPhone = '62' . substr($ownerPhone, 1);
                }
                
                SendWaNotif::dispatch([
                    'target' => $ownerPhone,
                    'msg' => $msg,
                ]);
            });
        });

        $this->redirect($this->getResource()::getUrl('index'));
    }
}
