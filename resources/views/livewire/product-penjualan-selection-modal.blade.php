<div class="dark:bg-gray-900 dark:text-gray-100 p-2 sm:p-4">
    @if($this->errorMessage)
        <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-md border border-red-200 dark:border-red-700">
            {{ $this->errorMessage }}
        </div>
    @endif
    
    <!-- FILTER -->
    <div class="mb-4">

        <!-- RADIO RESPONSIF -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-6 mb-3">
            <label class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                <input 
                    type="radio"
                    wire:model.live="searchType"
                    value="product_name"
                    class="h-4 w-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:ring-primary-500 dark:focus:ring-primary-600"
                >
                <span class="text-sm font-medium">Nama Produk</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                <input 
                    type="radio"
                    wire:model.live="searchType"
                    value="product_catalog"
                    class="h-4 w-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:ring-primary-500 dark:focus:ring-primary-600"
                >
                <span class="text-sm font-medium">Kode Produk</span>
            </label>
        </div>

        <!-- INPUT CARI -->
        <input 
            type="text" 
            wire:model.live.debounce.300ms="searchTerm"
            wire:keydown.enter.prevent="noop"
            placeholder="{{ $searchType === 'product_catalog' ? 'Masukkan katalog produk...' : 'Masukkan nama produk...' }}"
            class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-md 
                   focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
    </div>

    <!-- TABEL -->
    <div class="overflow-x-auto rounded-md border border-gray-300 dark:border-gray-700">
        <table class="min-w-full text-sm sm:text-base">
            
            <thead class="bg-primary-600 dark:bg-primary-700 text-gray-100 text-xs sm:text-sm">
                <tr>
                    <th class="w-15 sm:w-32 px-2 sm:px-4 py-2">Katalog</th>
                    <th class="px-2 sm:px-4 py-2">Produk</th>
                    <th class="w-15 px-2 sm:px-4 py-2">Stock</th>
                    <th class="w-20 px-2 sm:px-4 py-2">Aksi</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-900 text-xs sm:text-sm divide-y divide-gray-200 dark:divide-gray-800">
                
                @forelse($this->filteredBarang as $product)
                    @php
                        $isAdded = collect($products ?? [])->contains(fn($p) => $p['id'] === $product->id);
                    @endphp
                    <tr>

                        <!-- KATALOG -->
                        <td class="px-2 py-2 whitespace-nowrap text-gray-900 dark:text-gray-100">
                            {{ $product->product->product_catalog ?? '-' }}
                        </td>

                        <!-- NAMA -->
                        <td class="px-2 py-2">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $product->product->product_name }}
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <div>SN: {{ $product->product_stock_sn ?? '—' }}</div>
                                    <div>ED: {{ $product->product_stock_ed ?? '—' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-2 py-2">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $product->stock }} {{ $product->product->satuan->satuan_name }}
                            </div>
                        </td>
                        
                        <!-- AKSI -->
                        <td class="px-2 py-1 whitespace-nowrap">
                            @if($isAdded)
                                <x-filament::button 
                                    wire:click.stop="removeProductModal({{ $product->id }})"
                                    size="xs"
                                    color="gray"
                                    class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </x-filament::button>
                            @else
                                <x-filament::button 
                                    wire:click.stop="addProduct({{ $product->id }})"
                                    size="xs"
                                    color="danger"
                                    class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                                >
                                    Tambah
                                </x-filament::button>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada produk ditemukan
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-3 text-xs sm:text-sm">
        {{ $this->filteredBarang->links() }}
    </div>
</div>
