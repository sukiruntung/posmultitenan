<div class="hidden md:block">
    <div class="w-full overflow-x-auto">
        <div class="min-w-[900px] ">

            <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-100 text-gray-700 font-semibold dark:bg-gray-800 dark:text-white">
                    <tr>
                        <th class="w-12 px-3 py-2 text-center dark:bg-gray-700 dark:text-white">No</th>
                        <th class="px-3 py-2 dark:bg-gray-700 dark:text-white">Nama Produk</th>
                        <th class="w-32 px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Qty</th>
                        <th class="w-36 px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Harga</th>
                        <th class="w-32 px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Disc</th>
                        <th class="w-32 px-3 py-2 text-right dark:bg-gray-700 dark:text-white">Subtotal</th>
                        <th class="w-20 px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-200">
                    @foreach ($this->products as $id => $product)
                        <tr class="bg-white shadow-sm rounded-md hover:bg-gray-50" wire:key="row-{{ $id }}">

                            <!-- NO -->
                            <td class="px-3 py-3 text-center text-gray-700 dark:bg-gray-700 dark:text-white">
                                {{ $loop->iteration }}
                            </td>

                            <!-- NAMA + SN + ED -->
                            <td class="px-3 py-3  dark:bg-gray-700 dark:text-white auto-shrink">
                                <div class="font-medium text-gray-900 dark:bg-gray-700 dark:text-white">{{ $product['name'] }}</div>

                                <div class="text-xs text-gray-500 mt-1 leading-tight dark:bg-gray-700 dark:text-white">
                                    <div><span class="font-semibold">SN:</span> {{ $product['sn'] ?? '—' }}</div>
                                    <div><span class="font-semibold">ED:</span> {{ $product['ed'] ?? '—' }}</div>
                                </div>
                            </td>

                            <!-- QTY -->
                            <td class="px-3 py-3 text-center dark:bg-gray-700 dark:text-white">

                                <div class="flex justify-center items-center">
                                    <input type="number"
                                        wire:model.lazy="products.{{ $id }}.qty"
                                        wire:change="updateSubTotalPenjualan({{ $id }})"
                                        class="auto-shrink text-xs w-20 border rounded-l-md text-center h-9 dark:bg-gray-700 dark:text-white"
                                        onclick="this.select()">

                                    <span class="border border-l-0 rounded-r-md bg-gray-100 px-2 h-9 flex items-center text-xs dark:bg-gray-700 dark:text-white">
                                        {{ $product['satuan_name'] }}
                                    </span>
                                </div>

                                @if($product['errorQty'])
                                    <div class="text-red-500 text-xs mt-1">
                                        Qty melebihi stock
                                    </div>
                                @endif
                            </td>

                            <!-- HARGA -->
                            <td class="px-3 py-3 text-center relative dark:bg-gray-700 dark:text-white">

                                <select
                                    wire:model.lazy="products.{{ $id }}.harga_mode"
                                    wire:change="updateHargaMode({{ $id }})"
                                    class="auto-shrink text-xs border rounded-md px-2 py-1 w-28 h-9 text-right dark:bg-gray-700 dark:text-white">
                                    
                                    <option value="{{ $product['harga_default'] }}">
                                        {{ number_format($product['harga_default'], 0, ',', '.') }}
                                    </option>

                                    @foreach ($product['harga'] as $harga)
                                        @if ($harga != $product['harga_default'])
                                            <option value="{{ $harga }}">{{ number_format($harga, 0, ',', '.') }}</option>
                                        @endif
                                    @endforeach

                                    <option value="custom">Custom</option>
                                </select>

                                @if ($product['harga_mode'] === 'custom')
                                    <input type="number"
                                        wire:model.lazy="products.{{ $id }}.harga_default"
                                        wire:change="updateSubTotalPenjualan({{ $id }})"
                                        class="auto-shrink text-xs mt-2 w-28 border rounded-md text-center h-9 dark:bg-gray-700 dark:text-white"
                                        placeholder="Harga custom"
                                        onclick="this.select()">
                                @endif
                            </td>

                            <!-- DISCOUNT -->
                            <td class="px-3 py-3 text-center dark:bg-gray-700 dark:text-white">
                                <div class="flex items-center justify-center gap-1">

                                    <input type="number"
                                        wire:model.lazy="products.{{ $id }}.disc"
                                        wire:change="updateSubTotalPenjualan({{ $id }})"
                                        class="auto-shrink text-xs w-20 h-9 border rounded px-1 text-right dark:bg-gray-700 dark:text-white"
                                        placeholder="0">

                                    <select
                                        wire:model.lazy="products.{{ $id }}.disc_type"
                                        wire:change="updateSubTotalPenjualan({{ $id }})"
                                        class="text-xs w-16 border rounded h-9 text-center dark:bg-gray-700 dark:text-white">
                                        <option value="percent">%</option>
                                        <option value="rupiah">Rp</option>
                                    </select>
                                </div>
                            </td>

                            <!-- SUBTOTAL -->
                            <td class="px-3 py-3 text-right font-semibold dark:bg-gray-700 dark:text-white">
                                {{ number_format($product['subtotal'], 0, ',', '.') }}
                            </td>

                            <!-- DELETE -->
                            <td class="px-3 py-3 text-center dark:bg-gray-700 dark:text-white">
                                <x-filament::button color="danger" wire:click="removeProductModal('{{ $id }}')">
                                    Delete
                                </x-filament::button>
                                {{-- <button
                                    wire:click="removeProductModal('{{ $id }}')"
                                    class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-xs">
                                    Delete
                                </button> --}}
                            </td>

                        </tr>
                    @endforeach
                </tbody>

                <!-- FOOTER (TOTAL / DISC / PPN / ONGKIR / GRAND TOTAL) -->
                <tfoot class="bg-gray-100 text-sm dark:bg-gray-700 dark:text-white">
                    <tr class="mt-10 dark:bg-gray-700 dark:text-white">
                        <td colspan="5" class="text-right font-bold px-3 py-2">Total</td>
                        <td class="text-right px-3 py-2 font-semibold">
                            Rp {{ number_format($this->form->getState()['penjualan_barang_total'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" class="text-right font-bold px-3 py-2">Discount</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-1 justify-end">
                                <input type="number"
                                    wire:model.lazy="data.penjualan_barang_discount"
                                    wire:change="updateSubTotalPenjualan({{ $id }})"
                                    class="text-xs w-[70px] border rounded text-right px-1 dark:bg-gray-700 dark:text-white">

                                <select
                                    wire:model.lazy="data.penjualan_barang_discounttype"
                                    class="w-20 text-xs border rounded text-center dark:bg-gray-700 dark:text-white">
                                    <option value="percent">%</option>
                                    <option value="rupiah">Rp</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    @php
                        $menuAccess = auth()->user()->getCachedMenuAccess(3);
                    @endphp
                    @if($menuAccess && $menuAccess->can_ppn)
                        <tr>
                            <td colspan="5" class="text-right font-bold px-3 py-2">PPN Rate (%)</td>
                            <td class="text-right px-3 py-2">
                                <input type="number"
                                    wire:model.lazy="data.penjualan_barang_ppn"
                                    class="w-20 text-xs h-9 border rounded text-right dark:bg-gray-700 dark:text-white"
                                    readonly>
                            </td>
                        </tr>
                    @endif

                    @if($menuAccess && $menuAccess->can_ongkir)
                        <tr>
                            <td colspan="5" class="text-right font-bold px-3 py-2">Ongkos Kirim</td>
                            <td class="px-3 py-2 text-right">
                                <input type="number"
                                    wire:model.lazy="data.penjualan_barang_ongkir"
                                    wire:change="updateSubTotalPenjualan({{ $id }})"
                                    class="w-24 h-9 text-xs border rounded text-right px-1 dark:bg-gray-700 dark:text-white"
                                    placeholder="0">
                            </td>
                        </tr>
                    @endif

                    <tr class="bg-gray-200 dark:bg-gray-700 dark:text-white">
                        <td colspan="5" class="text-right font-bold px-3 py-3">Grand Total</td>
                        <td class="text-right px-3 py-3 font-extrabold text-lg">
                            Rp {{ number_format($this->form->getState()['penjualan_barang_grandtotal'], 0, ',', '.') }}
                        </td>
                    </tr>

                </tfoot>

            </table>
        </div>
    </div>
</div>
