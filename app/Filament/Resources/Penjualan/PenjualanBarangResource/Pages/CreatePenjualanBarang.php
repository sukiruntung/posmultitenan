<?php

namespace App\Filament\Resources\Penjualan\PenjualanBarangResource\Pages;

use App\Filament\Resources\Penjualan\PenjualanBarangResource;
use App\Jobs\SendWaNotif;
use Filament\Actions;
use Filament\Forms;
use App\Traits\TransaksiHelper;
use App\Models\Mitra\Customer;
use App\Models\Penjualan\PenjualanBarang as PenjualanPenjualanBarang;
use App\Models\Penjualan\PenjualanBarangDetail as PenjualanPenjualanBarangDetail;
use App\Models\Products\ProductStock;
use App\Services\NumberGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;

class CreatePenjualanBarang extends CreateRecord
{
    use TransaksiHelper;
    protected static string $resource = PenjualanBarangResource::class;

    public bool $isValidated = false;
    public $customer;
    public $customerID;

    public function mount(): void
    {
        parent::mount();
        session()->forget('list_productsPenjualan');
        // $this->products = [
        //     [
        //         'id' => 2,
        //         'product_id' => 3,
        //         'name' => 'Produk Dummy',
        //         'merk' => 'Merk Z',
        //         'satuan_id' => '1',
        //         'satuan_name' => 'Unit',
        //         'sn' => 'sn test',
        //         'ed' => '2025-08-19',
        //         'qty' => 0,
        //         'errorQty' => false,
        //         'harga' => array_unique([1000, 1500, 2000, 2500]),
        //         'harga_default' => 1500,
        //         'harga_mode' => 'default',
        //         'disc' => 0,
        //         'disc_type' => 'percent',
        //         'subtotal' => 0,
        //     ],
        //     [
        //         'id' => 3,
        //         'product_id' => 3,
        //         'name' => 'Produk Dummy',
        //         'merk' => 'Merk Z',
        //         'satuan_id' => '1',
        //         'satuan_name' => 'Unit',
        //         'sn' => 'hallo',
        //         'ed' => '',
        //         'qty' => 0,
        //         'errorQty' => false,
        //         'harga' => array_unique([2000, 3500, 4000, 5500]),
        //         'harga_default' => 3500,
        //         'harga_mode' => 'default',
        //         'disc' => 0,
        //         'disc_type' => 'percent',
        //         'subtotal' => 0,
        //     ],
        //     [
        //         'id' => 4,
        //         'product_id' => 10,
        //         'name' => 'test 4',
        //         'merk' => 'Merk Z',
        //         'satuan_id' => '3',
        //         'satuan_name' => 'Pcs',
        //         'sn' => 'sn test',
        //         'ed' => '2025-08-19',
        //         'qty' => 0,
        //         'errorQty' => false,
        //         'harga' => array_unique([100, 150, 200, 250]),
        //         'harga_default' => 150,
        //         'harga_mode' => 'default',
        //         'disc' => 0,
        //         'disc_type' => 'percent',
        //         'subtotal' => 0,
        //     ],
        // ];
        // $current = $this->form->getState();
        // $this->form->fill([
        //     ...$this->form->getState(),
        //     'customer_id' => 1,
        // ]);
    }

    protected function afterCreate(): void
    {
        $this->products = [];
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

    protected function handleRecordCreation(array $data): Model
    {
        $data['products'] = $this->products;
        if (empty($data['products'])) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Produk belum dipilih, silakan tambahkan minimal satu produk.')
                ->danger()
                ->send();

            throw new Halt();
        }

        return DB::transaction(function () use ($data) {

            $data['user_id'] = Auth::id();
            $data['penjualan_barang_no'] = NumberGenerator::generate($data['transaction_type'], $data['outlet_id']);
            unset($data['transaction_type']);
            $penerimaan = PenjualanPenjualanBarang::create($data);

            foreach ($data['products'] as $detailproduct) {
                if ($detailproduct['qty'] <= 0) {
                    Notification::make()
                        ->title('Gagal menyimpan')
                        ->body('ada produk dengan jumlah qty 0, silakan periksa kembali daftar produk.')
                        ->danger()
                        ->send();

                    throw new Halt();
                }
                PenjualanPenjualanBarangDetail::create([
                    'penjualan_barang_id' => $penerimaan->id,
                    'product_stock_id' => $detailproduct['id'],
                    'product_id' => $detailproduct['product_id'],
                    'penjualan_barang_detailproduct_name' => $detailproduct['name'],
                    'penjualan_barang_detail_sn' => $detailproduct['sn'],
                    'penjualan_barang_detail_ed' => $detailproduct['ed'] ?: null,
                    'penjualan_barang_detail_qty' => $detailproduct['qty'],
                    'penjualan_barang_detail_price' => $detailproduct['harga_default'],
                    'penjualan_barang_detail_discount' => $detailproduct['disc'],
                    'penjualan_barang_detail_total' => $detailproduct['subtotal'],
                    'penjualan_barang_detail_discounttype' => $detailproduct['disc_type'],
                    'user_id' =>  $data['user_id'],
                ]);
            }
            dd('kene');
            session()->forget('list_productsPenjualan');
            return $penerimaan;
        });
    }
    protected function getRedirectUrl(): string
    {
        // otomatis redirect ke halaman index resource setelah create
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void {}
    #[On('customer-changed')]
    public function handleCustomerChanged($customerId)
    {
        $this->customerID = $customerId;
        $this->customer = Customer::find($customerId);
        if (! $this->customer) return;

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
