<?php

namespace App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;

use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use App\Models\Mitra\SupplierProduct;
use App\Models\Pembelian\PenerimaanBarang;
use App\Models\Pembelian\PenerimaanBarangDetail;
use App\Models\Products\Product;
use App\Traits\TransaksiHelper;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePenerimaanBarang extends CreateRecord
{
    protected static string $resource = PenerimaanBarangResource::class;
    use TransaksiHelper;


    public bool $isValidated = false;

    public function mount(): void
    {
        parent::mount();
        session()->forget('list_products');
        // $this->products = [
        //     [
        //         'id' => 3,
        //         'name' => 'Produk Dummy',
        //         'merk' => 'Merk Z',
        //         'satuan_id' => '1',
        //         'satuan_name' => 'Unit',
        //         'sn' => '',
        //         'ed' => '',
        //         'qty' => 0,
        //         'harga' => 0,
        //         'disc' => 0,
        //         'disc_type' => 'percent',
        //         'subtotal' => 0,
        //     ],
        //     [
        //         'id' => 10,
        //         'name' => 'Produk Testing',
        //         'merk' => 'Merk Y',
        //         'satuan_id' => '3',
        //         'satuan_name' => 'Pcs',
        //         'sn' => '',
        //         'ed' => '',
        //         'qty' => 0,
        //         'harga' => 0,
        //         'disc' => 0,
        //         'disc_type' => 'percent',
        //         'subtotal' => 0,
        //     ],
        // ];
        // $current = $this->form->getState();

        // $this->form->fill(array_merge($current, [
        //     'selected_products' => [
        //         ['id' => 99, 'name' => 'Produk Dummy', 'merk' => 'Merk Z', 'satuan' => 'Unit'],
        //         ['id' => 100, 'name' => 'Produk Testing', 'merk' => 'Merk Y', 'satuan' => 'PCS'],
        //     ],
        // ]));
        // $this->form->fill([
        //     ...$this->form->getState(),
        //     'selected_products' => $dummyProducts,
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
                ->label('Pilih Product')
                ->modalContent(fn() => view('livewire.modal-pembelianbarang-wrapper')
                    ->with([
                        'products' => $this->products,
                    ]))
                ->modalSubmitAction(false)
                ->closeModalByClickingAway(false),

        ];
    }

    public function addProduct($productId)
    {
        $product = Product::with(['satuan', 'merk'])->find($productId);
        // dd($product);
        if (!$product) return;

        $exists = collect($this->products)->contains(fn($p) => $p['id'] === $product->id);

        if (!$exists) {
            $supplierId = $this->form->getState()['supplier_id'] ?? null;
            $supplierProduct = SupplierProduct::where('supplier_id', $supplierId)
                ->where('product_id', $product->id)
                ->first();

            $hargaBeli = $supplierProduct ? $supplierProduct->harga_beli : 0;
            $this->products[] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'merk' => $product->merk->merk_name ?? '',
                'satuan_id' => $product->satuan_id ?? 0,
                'satuan_name' => $product->satuan->satuan_name ?? '',
                'sn' => '',
                'ed' => '',
                'qty' => 0,
                'harga' => $hargaBeli,
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
        if (blank($formState['penerimaan_barang_invoicenumber'] ?? null)) {

            $this->dispatch('showValidationError', message: 'Harap isi No Pembelian terlebih dahulu');
            return;
        }
        if (blank($formState['supplier_id'] ?? null)) {

            $this->dispatch('showValidationError', message: 'Harap pilih Supplier terlebih dahulu');
            return;
        }

        $this->addProduct($productId);
    }

    public function syncProductsToModal()
    {
        session(['list_products' => $this->products]);
        $this->dispatch('syncProducts', products: $this->products);
    }
    protected function handleRecordCreation(array $data): Model
    {
        // unset($data['selected_products']);
        $data['products'] = $this->products;
        if (empty($data['products'])) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Produk belum dipilih, silakan tambahkan minimal satu produk.')
                ->danger()
                ->send();

            // biar proses create dibatalkan → lempar Exception
            throw new Halt();
        }
        $exists = PenerimaanBarang::where(
            'penerimaan_barang_invoicenumber',
            $data['penerimaan_barang_invoicenumber']
        )
            ->where('outlet_id', $data['outlet_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Nomor invoice sudah digunakan, silakan gunakan nomor lain.')
                ->danger()
                ->send();
            $this->dispatch('focus-input', name: 'penerimaan_barang_invoicenumber');
            throw new Halt(); // ⛔ stop proses create
        }
        return DB::transaction(function () use ($data) {
            // dd($data);
            $data['user_id'] = Auth::id();
            unset($data['transaction_type']);
            $penerimaan = PenerimaanBarang::create($data);

            foreach ($data['products'] as $detailproduct) {
                PenerimaanBarangDetail::create([
                    'penerimaan_barang_id' => $penerimaan->id,
                    'product_id' => $detailproduct['id'],
                    'penerimaan_barang_detailproduct_name' => $detailproduct['name'],
                    'penerimaan_barang_detail_sn' => $detailproduct['sn'],
                    'penerimaan_barang_detail_ed' => $detailproduct['ed'] ?: null,
                    'penerimaan_barang_detail_qty' => $detailproduct['qty'],
                    'penerimaan_barang_detail_price' => $detailproduct['harga'],
                    'penerimaan_barang_detail_discount' => $detailproduct['disc'],
                    'penerimaan_barang_detail_total' => $detailproduct['subtotal'],
                    'penerimaan_barang_detail_discounttype' => $detailproduct['disc_type'],
                    'user_id' =>  $data['user_id'],
                ]);
            }

            session()->forget('list_products');
            return $penerimaan;
        });
        // return parent::handleRecordCreation($data);
    }
    protected function getRedirectUrl(): string
    {
        // otomatis redirect ke halaman index resource setelah create
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        if (empty($this->products)) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Produk wajib dipilih minimal satu.')
                ->danger()
                ->send();

            $this->halt(); // stop proses create
        }
    }
}
