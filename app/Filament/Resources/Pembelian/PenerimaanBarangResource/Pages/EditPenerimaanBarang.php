<?php

namespace App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;

use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use App\Models\Mitra\SupplierProduct;
use App\Models\Pembelian\PenerimaanBarangDetail;
use App\Models\Products\Product;
use App\Traits\TransaksiHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPenerimaanBarang extends EditRecord
{
    protected static string $resource = PenerimaanBarangResource::class;
    use TransaksiHelper;
    public bool $isValidated = false;

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
        session(['list_products' => $this->products]);
        $this->form->fill([
            ...$this->form->getState(),
            'transaction_type' => 'penerimaan_barang',
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
        // unset($data['selected_products']);
        $data['products'] = $this->products;
        $data['user_id'] = Auth::id();

        unset($data['transaction_type']);
        DB::transaction(function () use ($record, $data) {
            // update header
            $record->update($data);

            // hapus detail lama
            $record->penerimaanBarangDetail()->delete();

            // insert detail baru
            foreach ($data['products'] as $detailproduct) {
                PenerimaanBarangDetail::updateOrCreate(
                    [
                        'penerimaan_barang_id' => $record->id,
                        'product_id' => $detailproduct['id'],
                    ],
                    [
                        'penerimaan_barang_detailproduct_name' => $detailproduct['name'],
                        'penerimaan_barang_detail_sn'         => $detailproduct['sn'],
                        'penerimaan_barang_detail_ed'         => $detailproduct['ed'] ?: null,
                        'penerimaan_barang_detail_qty'        => $detailproduct['qty'],
                        'penerimaan_barang_detail_price'      => $detailproduct['harga'],
                        'penerimaan_barang_detail_discount'   => $detailproduct['disc'],
                        'penerimaan_barang_detail_total'      => $detailproduct['subtotal'],
                        'penerimaan_barang_detail_discounttype' => $detailproduct['disc_type'],
                        'user_id'                             => $data['user_id'],
                    ]
                );
            }
        });

        session()->forget('list_products');
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
                ->label('Pilih Product')
                ->icon('heroicon-o-plus')
                ->modalHeading('Daftar Produk')
                ->modalWidth('7xl')
                ->modalSubmitActionLabel('Pilih')
                ->modalContent(fn() => view('livewire.modal-pembelianbarang-wrapper')
                    ->with([
                        'products' => $this->products,
                        'supplierId' => $this->form->getState()['supplier_id'] ?? null
                    ]))
                ->modalSubmitAction(false)
                ->closeModalByClickingAway(false),
            // ->modalContent(fn() => view('filament.components.pembelian-product-modal', [
            //     'searchType' => $this->searchType,
            //     'searchTerm' => $this->searchTerm,
            //     'paginatedProducts' => $this->getFilteredProductsForModal($this->searchType, $this->searchTerm),
            //     'currentPage' => $this->currentPage,
            //     'products' => $this->products
            // ]))
            // ->modalFooterActions([]),

        ];
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['products'] = $this->products;

        return $data;
    }

    public function addProduct($productId)
    {
        $product = Product::with(['satuan', 'merk'])->find($productId);

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
        // echo $index;
        $this->removeProduct($index);
        $this->syncProductsToModal();
    }
    public function syncProductsToModal()
    {
        session(['list_products' => $this->products]);
        $this->dispatch('syncProducts', products: $this->products);
    }
    public function validateAndAddProduct($productId)
    {
        $formState = $this->form->getState();
        if (blank($formState['penerimaan_barang_invoicenumber'] ?? null)) {

            $this->dispatch('showValidationError', message: 'Harap isi No Pembelian terlebih dahulu');
            return;
        }

        $this->addProduct($productId);
    }
}
