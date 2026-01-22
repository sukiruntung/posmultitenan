<div class="md:hidden space-y-4">
    @foreach ($this->products as $id => $product)
        <div class="border rounded-lg p-3 bg-white shadow-sm" wire:key="mobile-{{ $id }}">
            <div class="text-sm font-semibold text-gray-900">{{ $product['name'] }}</div>

            <div class="text-xs text-gray-500 mb-2">
                SN: {{ $product['sn'] ?? '—' }} • 
                ED: {{ $product['ed'] ?? '—' }} • 
                Satuan: {{ $product['satuan_name'] ?? '—' }}
            </div>

            {{-- QTY --}}
            <label class="text-xs font-semibold text-gray-700">Qty</label>
            <input type="number"
                wire:model.lazy="products.{{ $id }}.qty"
                wire:change="updateSubTotalPenjualan({{ $id }})"
                class="w-full border rounded p-1 mb-2 text-right"
                onclick="this.select()"
                @if($this->isValidated) disabled @endif
            >
                @if($product['errorQty'])
                    <div class="errorQty text-red-500 text-xs mt-1">
                        Qty melebihi stock ({{ $product['qty'] ?? 0 }})
                    </div>
                @endif

            {{-- Harga --}}
            <label class="text-xs font-semibold text-gray-700">Harga</label>
            <select
                wire:model.lazy="products.{{ $id }}.harga_mode"
                wire:change="updateHargaMode({{ $id }})"
                class="w-full border rounded p-1 mb-2"
                @if($this->isValidated) disabled @endif
            >
                <option value="{{$product['harga_default']}}">
                    {{ number_format($product['harga_default'] ?? 0, 0, ',', '.') }}
                </option>
                @foreach ($product['harga'] as $h)
                    @if ($h != $product['harga_default'])
                        <option value="{{ $h }}">{{ number_format($h, 0, ',', '.') }}</option>
                    @endif
                @endforeach
                <option value="custom">Custom</option>
            </select>

            @if($product['harga_mode'] === 'custom')
                <input type="number"
                    wire:model.lazy="products.{{ $id }}.harga_default"
                    wire:change="updateSubTotalPenjualan({{ $id }})"
                    class="w-full border rounded p-1 mb-2 text-right"
                    placeholder="Masukkan harga"
                    onclick="this.select()"
                >
            @endif

            {{-- Diskon --}}
            <label class="text-xs font-semibold text-gray-700">Diskon</label>
            <div class="flex gap-2 mb-2">
                <input type="number"
                    wire:model.lazy="products.{{ $id }}.disc"
                    wire:change="updateSubTotalPenjualan({{ $id }})"
                    class="w-1/2 border rounded p-1 text-right"
                    placeholder="0"
                    onclick="this.select()"
                >

                <select
                    wire:model.lazy="products.{{ $id }}.disc_type"
                    wire:change="updateSubTotalPenjualan({{ $id }})"
                    class="w-1/2 border rounded p-1"
                >
                    <option value="percent">%</option>
                    <option value="rupiah">Rp</option>
                </select>
            </div>

            {{-- Subtotal --}}
            <div class="text-right font-semibold text-gray-800">
                Subtotal: Rp {{ number_format($product['subtotal'] ?? 0, 0, ',', '.') }}
            </div>

            {{-- Aksi --}}
           @if(!$this->isValidated)
            <div class="mt-3">
                <x-filament::button 
                    color="danger"
                    size="xs"
                    class="w-full justify-center"
                    wire:click="removeProductModal('{{ $id }}')"
                >
                    Delete
                </x-filament::button>
            </div>
            @endif
        </div>
    @endforeach
    <!-- MOBILE TOTALS (VISIBLE ON SMALL SCREENS ONLY) -->
    <div class="md:hidden bg-gray-50 p-4 mt-4 rounded shadow space-y-3">

        <div class="flex justify-between">
            <span class="font-bold">Total</span>
            <span>
                Rp {{ number_format($this->form->getState()['penjualan_barang_total'] ?? 0, 0, ',', '.') }}
            </span>
        </div>

        <!-- Discount -->
        <div class="flex justify-between items-center">
            <span class="font-bold">Disc</span>
            <div class="flex items-center gap-1">
                <input type="number"
                    wire:model.lazy="data.penjualan_barang_discount"
                    class="w-20 border rounded px-1 text-right"
                    placeholder="0"
                    onclick="this.select()"
                    wire:change="updateSubTotalPenjualan({{ $id }})">

                <select
                    wire:model.lazy="data.penjualan_barang_discounttype"
                    wire:change="updateSubTotalPenjualan({{ $id }})"
                    class="w-16 border rounded text-right">
                    <option value="percent">%</option>
                    <option value="rupiah">Rp</option>
                </select>
            </div>
        </div>
            @php
                $menuAccess = auth()->user()->getCachedMenuAccess(3);
            @endphp
        @if($menuAccess && $menuAccess->can_ppn)
        <div class="flex justify-between items-center">
            <span class="font-bold">PPN (%)</span>
            <input type="number"
                wire:model.lazy="data.penjualan_barang_ppn"
                class="w-20 border rounded px-1 text-right"
                readonly>
        </div>
        @endif

        @if($menuAccess && $menuAccess->can_ongkir)
        <div class="flex justify-between items-center">
            <span class="font-bold">Ongkir</span>
            <input type="number"
                wire:model.lazy="data.penjualan_barang_ongkir"
                class="w-28 border rounded px-1 text-right"
                placeholder="0"
                onclick="this.select()"
                wire:change="updateSubTotalPenjualan({{ $id }})">
        </div>
        @endif

        <hr>

        <div class="flex justify-between text-lg font-bold">
            <span>Grand Total</span>
            <span>
                Rp {{ number_format($this->form->getState()['penjualan_barang_grandtotal'] ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    
</div>