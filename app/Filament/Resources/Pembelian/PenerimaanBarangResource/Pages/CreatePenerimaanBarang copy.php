<?php

namespace App\Filament\Resources\Pembelian\PenerimaanBarangResource\Pages;

use App\Filament\Resources\Pembelian\PenerimaanBarangResource;
use App\Models\Pembelian\PenerimaanBarangDetail;
use App\Models\Products\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanBarang extends CreateRecord
{
    protected static string $resource = PenerimaanBarangResource::class;

    public $selectedProducts = [];
    public array $qty = [];
    public array $harga = [];
    public array $subtotal = [];
    public array $disc = [];
    public array $disc_type = [];
    public $products = [];
    // public $dummyProducts = [];

    public function mount(): void
    {
        parent::mount();
        $dummyProducts = [
            [
                'id' => 99,
                'name' => 'Produk Dummy',
                'merk' => 'Merk Z',
                'satuan' => 'Unit',
                'qty' => 0,
                'harga' => 0,
                'disc' => 0,
                'disc_type' => 'persen',
                'subtotal' => 0,
            ],
            [
                'id' => 100,
                'name' => 'Produk Testing',
                'merk' => 'Merk Y',
                'satuan' => 'PCS',
                'qty' => 0,
                'harga' => 0,
                'disc' => 0,
                'disc_type' => 'persen',
                'subtotal' => 0,
            ],
        ];
        // $current = $this->form->getState();

        // $this->form->fill(array_merge($current, [
        //     'selected_products' => [
        //         ['id' => 99, 'name' => 'Produk Dummy', 'merk' => 'Merk Z', 'satuan' => 'Unit'],
        //         ['id' => 100, 'name' => 'Produk Testing', 'merk' => 'Merk Y', 'satuan' => 'PCS'],
        //     ],
        // ]));
        $this->form->fill([
            ...$this->form->getState(),
            'selected_products' => $dummyProducts,
        ]);
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
                ->form([
                    \Filament\Forms\Components\Select::make('product_id')

                        ->label('Product')
                        ->options(Product::with(['satuan', 'merk'])
                            ->get()
                            ->mapWithKeys(function ($product) {
                                return [
                                    $product->id => $product->product_name
                                        . ' ( ' . ($product->merk->name ?? '')   . ' ) '
                                        . ' - ' . ($product->satuan->satuan_name ?? '')
                                ];
                            }))
                        ->multiple()
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data, $livewire) {
                    // contoh: isi otomatis ke field di form utama
                    $products = Product::with(['satuan', 'merk'])
                        ->whereIn('id', $data['product_id'])
                        ->get()
                        ->map(fn($product) => [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'merk' => $product->merk->merk_name ?? '',
                            'satuan' => $product->satuan->satuan_name ?? '',
                        ])
                        ->toArray();

                    // Isi ke form state "selected_products"
                    $current = $livewire->form->getState()['selected_products'] ?? [];
                    $livewire->form->fill([
                        ...$this->form->getState(),
                        'selected_products' => array_merge($current, $products),
                    ]);
                }),
        ];
    }
    public function removeProduct($productId)
    {
        $products = $this->form->getState()['selected_products'] ?? [];

        // filter array, buang product yang dihapus
        $products = array_filter($products, function ($item) use ($productId) {
            return $item['id'] != $productId;
        });

        // reset index biar rapih
        $products = array_values($products);

        // isi ulang ke form state
        $this->form->fill([
            ...$this->form->getState(),
            'selected_products' => $products,
        ]);
    }
    public function updateSubtotal($productId)
    {
        // print_r($this->qty[$productId]);
        $qty   = $this->qty[$productId] ?? 0;
        $harga = $this->harga[$productId] ?? 0;
        $disc = $this->disc[$productId] ?? 0;
        // echo isset($disc);
        if ($disc > 0) {
            $disc_type = $this->disc_type[$productId] ?? 'percent';
            if ($disc_type === 'percent') {
                $discountAmount = $qty * $harga * $this->disc[$productId] / 100;
            } else {
                // print_r($qty * $harga - $this->disc[$productId]);
                $discountAmount =  $this->disc[$productId];
            }
        } else {
            $discountAmount = 0;
        }

        $this->subtotal[$productId] = $qty * $harga - $discountAmount;
    }
}
