

<div class="dark:bg-gray-900 dark:text-gray-100">

    <!-- Radio Button -->
    <div class="mb-4">
        <div class="flex gap-6 mb-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input 
                    type="radio" 
                    wire:model.live="searchType" 
                    value="product_name" 
                    class="h-4 w-4"
                >
                <span class="text-sm font-medium">Nama Produk</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer">
                <input 
                    type="radio" 
                    wire:model.live="searchType" 
                    value="product_catalog" 
                    class="h-4 w-4"
                >
                <span class="text-sm font-medium">Kode Produk</span>
            </label>
        </div>

        <!-- Input -->
        <input 
            type="text" 
            wire:model.live="searchTerm"
            placeholder="{{ $searchType === 'product_catalog' ? 'Masukkan katalog produk...' : 'Masukkan nama produk...' }}"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-md 
                   focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

            <!-- HEADER TABLE -->
            <thead class="bg-primary-600 dark:bg-primary-700">
                <tr>
                    <th class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                        Katalog
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                        Produk
                    </th>
                    <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                        Satuan
                    </th>
                    <th class="w-20 px-4 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>

            <!-- BODY -->
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($paginatedProducts->items() as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">

                        <!-- KATALOG (pendek) -->
                        <td class="px-3 py-1 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            {{ $product->product_catalog ?? '-' }}
                        </td>

                        <!-- NAMA PRODUK (lebih lebar & rapi) -->
                        <td class="px-3 py-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ $product->product_name  }}
                            </div>
                        </td>

                        <!-- KOLOM STOK TERPISAH -->
                        <td class="px-3 py-1 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                            {{ $product->satuan->satuan_name ?? '-' }}
                        </td>

                        <!-- AKSI -->
                        <td class="px-3 py-1 whitespace-nowrap text-sm font-medium">
                            @php
                                $isAdded = collect($products ?? [])->contains(fn($p) => $p['id'] === $product->id);
                            @endphp
                            
                            @if($isAdded)
                                <x-filament::button 
                                    wire:click.stop="removeProductModal({{ $product->id }})" 
                                    size="sm" 
                                    color="gray"
                                >
                                    Cancel
                                </x-filament::button>
                            @else
                                <x-filament::button 
                                    wire:click.stop="addProduct({{ $product->id }})" 
                                    size="sm" 
                                    color="danger"
                                >
                                    Tambah
                                </x-filament::button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada produk ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- Pagination -->
    @if($paginatedProducts->hasPages())
    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Menampilkan {{ $paginatedProducts->firstItem() }} sampai {{ $paginatedProducts->lastItem() }} dari {{ $paginatedProducts->total() }} produk
        </div>
        <div class="flex space-x-1">
            @if($paginatedProducts->onFirstPage())
                <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded cursor-not-allowed">Sebelumnya</span>
            @else
                <button wire:click.stop="previousPage" class="px-3 py-1 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Sebelumnya</button>
            @endif

            @for($page = 1; $page <= $paginatedProducts->lastPage(); $page++)
                @if($page == $paginatedProducts->currentPage())
                    <span class="px-3 py-1 text-sm text-white bg-blue-600 rounded">{{ $page }}</span>
                @else
                    <button wire:click.stop="goToPage({{ $page }})" class="px-3 py-1 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $page }}</button>
                @endif
            @endfor

            @if($paginatedProducts->hasMorePages())
                <button wire:click.stop="nextPage" class="px-3 py-1 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Selanjutnya</button>
            @else
                <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 rounded cursor-not-allowed">Selanjutnya</span>
            @endif
        </div>
    </div>
    @endif

</div>
