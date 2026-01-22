<?php

namespace App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;

use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use App\Models\Pembelian\PenerimaanBarangDetail;
use App\Models\Products\ProductStock;
use App\Models\Products\ProductStockHistories;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class UnValidatePenerimaanBarang extends EditRecord
{
    protected static string $resource = PenerimaanBarangResource::class;
    public $products = [];
    public bool $isValidated = true;
    // protected static string $view = 'filament.resources.penerimaan-barang.pages.validate'; // optional, kalau mau ganti blade

    public function getTitle(): string
    {
        return 'UnValidasi Penerimaan Barang';
    }
    public function mount($record): void
    {
        parent::mount($record);
        $this->products = PenerimaanBarangDetail::with('product', 'product.satuan', 'product.merk')
            ->where('penerimaan_barang_id', $record)
            ->get()
            ->map(fn($detail) => [
                'id'         => $detail->product_id,
                'name'       => $detail->penerimaan_barang_detailproduct_name,
                'merk'       => $detail->product->merk->merk_name ?? '',
                'satuan_id'  => $detail->product->satuan_id,
                'satuan_name' => $detail->product->satuan->satuan_name ?? '',
                'sn'         => $detail->penerimaan_barang_detail_sn,
                'ed'         => $detail->penerimaan_barang_detail_ed,
                'qty'        => $detail->penerimaan_barang_detail_qty,
                'harga'      => $detail->penerimaan_barang_detail_price,
                'disc'       => $detail->penerimaan_barang_detail_discount,
                'disc_type'  => $detail->penerimaan_barang_detail_discounttype,
                'subtotal'   => $detail->penerimaan_barang_detail_total,
            ])
            ->toArray();


        $this->form->fill([
            ...$this->form->getState(),
            'transaction_type' => 'penerimaan_barang',
        ]);
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
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
                $productStock = ProductStock::where('product_id', $detail['id'])
                    ->where('product_stock_sn', $detail['sn'])
                    ->where('product_stock_ed', $detail['ed'])
                    ->first();
                if (!$productStock) {
                    $productStock = ProductStock::create([
                        'product_id'        => $detail['id'],
                        'product_stock_sn'  => $detail['sn'],
                        'product_stock_ed'  => $detail['ed'],
                        'stock'             => $detail['qty'],
                        'user_id'          => Auth::id(),
                    ]);
                    $cekHistory = ProductStockHistories::where('product_stock_id', $productStock->id)
                        ->where('no_transaksi', $data['penerimaan_barang_invoicenumber'])->exists();
                    if ($cekHistory) {
                        Notification::make()
                            ->title('Gagal menyimpan data')
                            ->body('Data history untuk produk ' . $detail['name'] . ' sudah ada.')
                            ->danger()
                            ->send();

                        return null;
                    } else {
                        ProductStockHistories::create([
                            'tanggal' => now(),
                            'product_stock_id' => $productStock->id,
                            'qty_masuk'        => $detail['qty'],
                            'stock_awal'       => 0,
                            'stock_akhir'      => $detail['qty'],
                            'harga_beli'       => $detail['harga'],
                            'total_biaya_beli' => $detail['subtotal'],
                            'jenis'            => 'barang masuk',
                            'keterangan'       => $data['notes'],
                            'no_transaksi'     => $data['penerimaan_barang_invoicenumber'],
                            'user_id'          => Auth::id(),
                        ]);
                    }
                } else {
                    $stockAwal = $productStock['stock'];
                    $stockAkhir = $detail['qty'] + $stockAwal;

                    $totalHargaBeli = $stockAkhir * $detail['harga'];
                    $productStock->update([
                        'stock' => $detail['qty'] + $productStock['stock']
                    ]);
                    $cekHistory = ProductStockHistories::where('product_stock_id', $productStock->id)
                        ->where('no_transaksi', $data['penerimaan_barang_invoicenumber'])->exists();
                    if ($cekHistory) {
                        Notification::make()
                            ->title('Gagal menyimpan data')
                            ->body('Data history untuk produk ' . $detail['name'] . ' sudah ada.')
                            ->danger()
                            ->send();

                        return null;
                    } else {
                        ProductStockHistories::create([
                            'tanggal' => now(),
                            'product_stock_id'  => $productStock['id'],
                            'qty_masuk'         => $detail['qty'],
                            'stock_awal'        => $stockAwal,
                            'stock_akhir'       => $stockAkhir,
                            'harga_beli'       => $detail['harga'],
                            'total_biaya_beli'  => $totalHargaBeli,
                            'jenis'             => 'barang masuk',
                            'keterangan'        => 'barang masuk',
                            'keterangan'       => $data['notes'],
                            'no_transaksi'     => $data['penerimaan_barang_invoicenumber'],
                            'user_id'           => Auth::id(),
                        ]);
                    }
                }
            }

            $this->record->update([
                'penerimaan_barang_validatedby' => Auth::id(),
                'penerimaan_barang_validatedat' => now(),
                'penerimaan_barang_status'      => 'validated',
            ]);
        });

        $this->redirect($this->getResource()::getUrl('index'));
    }
}
