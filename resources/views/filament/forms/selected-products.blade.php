
@vite('resources/css/app.css')
@if (!empty($this->products)) 
   @php
    $menuAccess = auth()->user()->getCachedMenuAccess($menu_id);
    @endphp  

    <div class="text-gray-600 font-semibold mb-2">
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
            Detail product
        </h1>
    </div>

    <div class="w-full overflow-x-auto">
        <div class="min-w-[900px]">
            <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-gray-700 font-medium dark:bg-gray-800 dark:text-white">
                    <tr>
                        <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">No</th>
                        <th class="w-[200px] px-3 py-2 dark:bg-gray-700 dark:text-white">Nama Produk</th>
                        <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">SN</th>
                        <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">ED</th>
                        <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Qty</th>
                   
                        @if($menuAccess && $menuAccess->can_hargapembelian)
                            <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Harga</th>
                            <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Disc</th>
                            <th class="px-3 py-2 text-right dark:bg-gray-700 dark:text-white">Subtotal</th>
                        @endif
                        <th class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-200">
                    @foreach ($this->products as $id => $product)
                        <tr class="hover:bg-gray-50" wire:key="row-{{ $id }}">
                            <td class="px-3 py-2 text-gray-700 dark:bg-gray-700 dark:text-white"> {{  $loop->iteration  }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:bg-gray-700 dark:text-white auto-shrink">
                                <div class="font-medium text-gray-900 dark:bg-gray-700 dark:text-white">{{ $product['name'] ?? '—' }}</div>

                                <div class="text-xs text-gray-500 mt-1 leading-tight dark:bg-gray-700 dark:text-white">
                                    <div><span class="font-semibold">Merk:</span> {{ $product['merk'] ?? '—' }}</div>
                                </div>
                            </td>
                           <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white ">
                                <input type="text" wire:model="products.{{ $id }}.sn"
                                    class="auto-shrink text-xs w-30 border-gray-300 rounded-md text-center focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700"
                                    @if($this->isValidated) disabled @endif
                                    >
                            </td>
                            <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white ">
                                <input type="text" wire:model="products.{{ $id }}.ed"
                                    class=" text-xs w-23 flatpickr-input border-gray-300 rounded-md text-center focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700"
                                    @if($this->isValidated) disabled @endif>
                            </td>
                            
                            <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white ">
                                
                                <div class="flex justify-center items-center">
                                    <input type="number" name="products.{{ $id }}.qty" 
                                        wire:model.lazy="products.{{ $id }}.qty"
                                        class="w-15 text-xs border-gray-300 rounded-md text-center focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700"
                                        wire:change="updateSubtotal({{ $id }})"
                                        onclick="this.select()"
                                        @if($this->isValidated) disabled @endif>
                                    <span class="border border-l-0 rounded-r-md bg-gray-100 px-2 h-9 flex items-center text-xs dark:bg-gray-700 dark:text-white">
                                        {{ $product['satuan_name'] }}
                                    </span>
                                </div>
                            </td>
                        
                            @if($menuAccess && $menuAccess->can_hargapembelian)
                                <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white relative">
                                    <input type="number" name="products.{{ $id }}.harga"  placeholder="0"
                                    wire:model.lazy="products.{{ $id }}.harga"
                                        class="w-24 text-xs border-gray-300 rounded-md text-right focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 "
                                        wire:change="updateSubtotal({{ $id }})"
                                        onclick="this.select()"
                                        @if($this->isValidated) disabled @endif
                                    >
                                        <div 
                                        wire:loading 
                                        wire:target="updateSubtotal({{ $id }})" 
                                        class="absolute inset-0 flex items-center justify-center bg-white/60 dark:bg-gray-700/60"
                                        >
                                                <svg class="animate-spin h-24 w-24 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                        </div>
                                    
                                </td>
                                <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">
                                    <div class="flex items-center gap-1">
                                        <!-- input angka -->
                                        <input type="number" 
                                            name="products.{{ $id }}.disc" 
                                            wire:model.lazy="products.{{ $id }}.disc"
                                            class="w-[70px] auto-shrink text-xs border rounded px-1 text-right dark:bg-gray-700 dark:text-white"
                                            placeholder="0"
                                            wire:change="updateSubtotal({{ $id }})"
                                            onclick="this.select()"
                                            @if($this->isValidated) disabled @endif>
                                        
                                        <!-- pilih tipe diskon -->
                                        <select name="products.{{ $id }}.disc_type" 
                                        wire:model.lazy="products.{{ $id }}.disc_type"
                                        wire:change="updateSubtotal({{ $id }})"
                                                class="w-17 text-xs border-gray-300 rounded-md text-right focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700"
                                                @if($this->isValidated) disabled @endif>
                                            <option value="percent" selected>%</option>
                                            <option value="rupiah">Rp</option>
                                        </select>
                                    </div>
                                    
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-800 dark:bg-gray-700 dark:text-white relative">
                                    {{ number_format($product['subtotal'] ?? 0, 0, ',', '.') }}
                                </td>
                            
                            @endif
                            <td class="px-3 py-2 text-center dark:bg-gray-700 dark:text-white">   
                                        @if(!$this->isValidated)    
                                <x-filament::button color="danger" wire:click="removeProduct('{{ $id }}')">
                                    Delete
                                </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 text-sm dark:bg-gray-700 dark:text-white">
                    
                    @if($menuAccess && $menuAccess->can_hargapembelian)
                        <tr class="mt-10 dark:bg-gray-700 dark:text-white">
                            <td colspan="7" class="text-right font-bold px-3 py-2">Total</td>
                            <td class="text-right px-3 py-2 font-semibold">
                                Rp {{ number_format($this->form->getState()['penerimaan_barang_total'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right font-bold px-3 py-2">Disc</td>
                            <td class="text-right px-3 py-2">
                                <div class="flex items-center gap-1">
                                        <!-- input angka -->
                                        <input type="number" 
                                            wire:model.lazy="data.penerimaan_barang_discount"
                                            class="auto-shrink w-[70px] text-xs border rounded px-1 text-right dark:bg-gray-700 dark:text-white"
                                            placeholder="0"
                                            wire:change="updateSubtotal({{ $id }})"
                                            @if($this->isValidated) disabled @endif
                                        >
                                        
                                        <!-- pilih tipe diskon -->
                                        <select 
                                        wire:model.lazy="data.penerimaan_barang_discounttype"
                                        wire:change="updateSubtotal({{ $id }})"
                                                class="w-17 text-xs border-gray-300 rounded-md text-right focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700"
                                                @if($this->isValidated) disabled @endif>
                                            <option value="percent" selected>%</option>
                                            <option value="rupiah">Rp</option>
                                        </select>
                                    </div>
                            </td>
                        </tr>
                        <tr class="bg-gray-200 dark:bg-gray-700 dark:text-white">
                            <td colspan="7" class="text-right font-bold px-3 py-2">Grand Total</td>
                            <td class="text-right px-3 py-3 font-extrabold text-lg">
                                Rp {{ number_format($this->form->getState()['penerimaan_barang_grandtotal'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
@else 
    <div class="text-gray-500 italic">Belum ada produk dipilih.</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            flatpickr('.flatpickr-input', {
               dateFormat: "Y-m-d",      // format yang DIKIRIM ke backend
                altInput: true,
                altFormat: "d/m/Y",       // format yang DITAMPILKAN ke user
                // allowInput: true
                });
        }, 100);
        shrinkAllInputs();
    });
     function shrinkInputFont(el) {
        // debug sedikit (hapus/komentari setelah OK)
        // console.log("shrinkInputFont:", el, "val:", el.value ?? el.textContent);

        const maxFont = 14;
        const minFont = 10;
        let fontSize = maxFont;

        el.style.fontSize = maxFont + "px";

        // kecilkan sampai muat
        while (el.scrollWidth > el.clientWidth && fontSize > minFont) {
            fontSize--;
            el.style.fontSize = fontSize + "px";
        }
    }

    function shrinkAllInputs() {
        const els = document.querySelectorAll(".auto-shrink");
        if (!els.length) return;
        els.forEach(el => shrinkInputFont(el));
    }

    // shrink saat user mengetik (realtime)
    document.addEventListener("input", function (e) {
        if (e.target.classList && e.target.classList.contains("auto-shrink")) {
            shrinkInputFont(e.target);
        }
    });

    
</script>
@endpush
