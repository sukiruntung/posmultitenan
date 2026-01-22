<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <x-heroicon-o-shopping-cart class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Laporan Pembelian Periode
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Pilih periode untuk generate laporan pembelian
                    </p>
                </div>
            </div>

            <form wire:submit="generateReport">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5 mr-2" />
                        Generate Laporan
                    </button>
                    
                    <a href="{{ \App\Filament\Resources\Laporan\LaporanResource::getUrl('index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>