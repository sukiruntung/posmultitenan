<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                    <x-heroicon-o-cube class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Laporan Stock Opname
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Pilih tanggal dan kategori untuk generate laporan stock opname
                    </p>
                </div>
            </div>

            <form wire:submit="generateReport">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
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