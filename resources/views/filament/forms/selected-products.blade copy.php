
@vite('resources/css/app.css')
@if ($getState())
{{-- {{print_r($this->subtotal)}} --}}
    <div class="text-gray-600 font-semibold mb-2">
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
            Detail product
        </h1>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 text-gray-700 font-medium dark:bg-gray-800 dark:text-white">
                <tr>
                    <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">No</th>
                    <th class="px-3 py-2 dark:bg-gray-700 dark:text-white">Nama Produk</th>
                    <th class="px-3 py-2 dark:bg-gray-700 dark:text-white">Merk</th>
                    <th class="px-3 py-2 dark:bg-gray-700 dark:text-white">Satuan</th>
                    <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Qty</th>
                    <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Harga</th>
                    <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Disc</th>
                    <th class="px-3 py-2 text-right dark:bg-gray-700 dark:text-white">Subtotal</th>
                    <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-200">
                @foreach ($getState() as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-700 dark:bg-gray-700 dark:text-white">{{  $loop->iteration  }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900 dark:bg-gray-700 dark:text-white">{{ $product['name'] }}</td>
                        <td class="px-3 py-2 text-gray-600 dark:bg-gray-700 dark:text-white">{{ $product['merk'] }}</td>
                        <td class="px-3 py-2 text-gray-600 dark:bg-gray-700 dark:text-white">{{ $product['satuan'] }}</td>
                        <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white ">
                            <input type="number" name="qty[{{ $product['id'] }}]" 
                            wire:model="qty.{{ $product['id'] }}"
                                class="w-25 border-gray-300 rounded-md text-center focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700">
                        </td>
                        <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">
                            <input type="number" name="harga[{{ $product['id'] }}]"  placeholder="0"
                             wire:model.blur="harga.{{ $product['id'] }}"
                                class="w-33 border-gray-300 rounded-md text-right focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 "
                                 wire:change="updateSubtotal({{ $product['id'] }})"
                               >
                        </td>
                        <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">
                            <div class="flex items-center gap-1">
                                <!-- input angka -->
                                <input type="number" 
                                    name="disc[{{ $product['id'] }}]" 
                                      wire:model.blur="disc.{{ $product['id'] }}"
                                    class="w-[70px] border rounded px-1 text-right"
                                    placeholder="0"
                                    wire:change="updateSubtotal({{ $product['id'] }})">
                                
                                <!-- pilih tipe diskon -->
                                <select name="disc_type[{{ $product['id'] }}]" 
                                wire:model.blur="disc_type.{{ $product['id'] }}"
                                wire:change="updateSubtotal({{ $product['id'] }})"
                                        class="w-20 border-gray-300 rounded-md text-right focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700">
                                    <option value="percent" selected>%</option>
                                    <option value="rupiah">Rp</option>
                                </select>
                            </div>
                            
                        </td>
                        <td class="px-3 py-2 text-right font-semibold text-gray-800 dark:bg-gray-700 dark:text-white">
                            {{-- {{$this->subtotal[$product['id']] ?? 0}} --}}
                            {{ number_format($this->subtotal[$product['id']] ?? 0, 0, ',', '.') }}
                            {{-- {{ number_format($subtotal[$product['id']] ?? 0, 0, ',', '.') }} --}}
                        </td>
                        <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">                
                            <x-filament::button color="danger" wire:click="removeProduct('{{ $product['id'] }}')">
                                Delete
                            </x-filament::button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-gray-500 italic">Belum ada produk dipilih.</div>
@endif
