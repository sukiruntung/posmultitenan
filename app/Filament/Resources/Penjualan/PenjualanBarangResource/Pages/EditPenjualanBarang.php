<?php

namespace App\Filament\Resources\Penjualan\PenjualanBarangResource\Pages;

use App\Filament\Resources\Penjualan\PenjualanBarangResource;
use App\Models\Mitra\Customer;
use App\Models\Penjualan\PenjualanBarangDetail;
use App\Models\Products\ProductStock;
use App\Traits\TransaksiHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class EditPenjualanBarang extends EditRecord
{

    use TransaksiHelper;
    protected static string $resource = PenjualanBarangResource::class;

    public bool $isValidated = false;
    public $customer;
    public $customerID;

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
                'errorQty'  => false,
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

        session(['list_productsPenjualanPenjualan' => $this->products]);
        $this->customerID = $this->form->getState()['customer_id'] ?? null;
        $this->form->fill([
            ...$this->form->getState(),
            'transaction_type' => 'penjualan_barang',
        ]);
    }
    protected function getListeners(): array
    {
        return [
            'addProduct' => 'addProduct',
            'removeProductModal' => 'removeProductModal',
            'productsUpdated' => 'refreshProducts',
            'checkInvoiceNumber' => 'validateAndAddProduct'
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['products'] = $this->products;
        $data['user_id'] = Auth::id();

        unset($data['transaction_type']);
        DB::transaction(function () use ($record, $data) {
            $record->update($data);
            $idDetail = [];
            foreach ($data['products'] as $detailproduct) {
                $detail =  PenjualanBarangDetail::updateOrCreate(
                    [
                        'penjualan_barang_id' => $record->id,
                        'product_stock_id' => $detailproduct['id'],
                    ],
                    [
                        'product_id' => $detailproduct['product_id'],
                        'penjualan_barang_detailproduct_name' => $detailproduct['name'],
                        'penjualan_barang_detail_sn' => $detailproduct['sn'],
                        'penjualan_barang_detail_ed' => $detailproduct['ed'] ?: null,
                        'penjualan_barang_detail_qty' => $detailproduct['qty'],
                        'penjualan_barang_detail_price' => $detailproduct['harga_default'],
                        'penjualan_barang_detail_discount' => $detailproduct['disc'],
                        'penjualan_barang_detail_total' => $detailproduct['subtotal'],
                        'penjualan_barang_detail_discounttype' => $detailproduct['disc_type'],
                        'user_id' => $data['user_id'],
                    ]
                );
                array_push($idDetail, $detail->id);
            }
            PenjualanBarangDetail::where('penjualan_barang_id', $record->id)
                ->whereNotIn('id', $idDetail)
                ->delete();
        });

        session()->forget('list_productsPenjualan');
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        // setelah update balik ke index
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pilih_product')
                ->extraModalFooterActions([])
                ->label('Pilih Product')
                ->icon('heroicon-o-plus')
                ->modalHeading('Daftar Produk')
                ->modalWidth('7xl')
                ->modalContent(fn() => view('livewire.modal-penjualanbarang-wrapper')
                    ->with(['customerID' => $this->customerID]))
                ->modalSubmitAction(false)
                ->closeModalByClickingAway(false),


        ];
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['products'] = $this->products;

        return $data;
    }
    public function addProduct($productId)
    {
        $formState = $this->form->getState();
        $productstock = ProductStock::with(['product.satuan', 'product.merk'])->find($productId);

        if (!$productstock) return;
        $customer = $this->customer;
        $exists = collect($this->products)->contains(fn($p) => $p['id'] === $productstock->id);

        if (!$exists) {
            $hargaDefault = match ($customer->customer_harga ?? 'harga3') {
                'hargagrosir' => $productstock->product->hargajualgrosir,
                'harga2' => $productstock->product->hargajual2,
                'harga3' => $productstock->product->hargajual3,
                default => $productstock->product->hargajual1,
            };
            $this->products[] = [
                'id' => $productstock->id,
                'product_id' => $productstock->product_id,
                'name' => $productstock->product->product_name,
                'merk' => $productstock->product->merk->merk_name ?? '',
                'satuan_id' => $productstock->product->satuan_id ?? 0,
                'satuan_name' => $productstock->product->satuan->satuan_name ?? '',
                'sn' => $productstock->product_stock_sn,
                'ed' => $productstock->product_stock_ed,
                'qty' => 0,
                'errorQty' => false,
                'harga' => array_unique(
                    [
                        $productstock->product->hargajualgrosir,
                        $productstock->product->hargajual1,
                        $productstock->product->hargajual2,
                        $productstock->product->hargajual3
                    ]
                ),
                'harga_default' => $hargaDefault,
                'harga_mode' => 'default',
                'disc' => 0,
                'disc_type' => 'percent',
                'subtotal' => 0,
            ];
            $this->syncProductsToModal();
        }
    }

    public function removeProductModal($productId)
    {
        $index = array_search($productId, array_column($this->products, 'id'));
        $this->removeProduct($index);
        $this->syncProductsToModal();
    }

    public function validateAndAddProduct($productId)
    {
        $formState = $this->form->getState();
        if (blank($formState['customer_id'] ?? null)) {

            $this->dispatch('showValidationError', message: 'Harap pilih Customer terlebih dahulu');
            return;
        }

        $this->addProduct($productId);
    }
    public function syncProductsToModal()
    {
        session(['list_productsPenjualan' => $this->products]);
        $this->dispatch('syncProducts', products: $this->products);
    }

    #[On('customer-changed')]
    public function handleCustomerChanged($customerId)
    {
        $this->customerID = $customerId;
        $customer = Customer::find($customerId);
        if (!$customer) return;

        // Update harga default setiap product di $this->products
        foreach ($this->products as $index => &$product) {
            $productStock = ProductStock::with('product')->find($product['id']);

            $product['harga_default'] = match ($customer->customer_harga ?? 'harga1') {
                'hargagrosir' => $productStock->product->hargajualgrosir,
                'harga2' => $productStock->product->hargajual2,
                'harga3' => $productStock->product->hargajual3,
                default => $productStock->product->hargajual1,
            };
            $this->updateSubTotalPenjualan($index);
        }
    }
}
