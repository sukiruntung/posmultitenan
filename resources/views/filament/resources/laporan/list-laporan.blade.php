
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <form wire:submit="generateReport">
            {{ $this->form }}
            
            @if($this->data['laporan_id'] ?? null)
            <div class="flex items-center gap-3 mt-6">
                <x-filament::button
                    wire:click="printReport"
                    color="primary"
                    icon="heroicon-o-document-chart-bar"
                >
                    Cetak Laporan
                </x-filament::button>
                
                
            </div>
            @endif
        </form>
        
        @if($this->url)
        <div class="w-full reportModal mt-6">
            <iframe 
                src="{{ $this->url }}"
                class="w-full h-full border rounded"
            ></iframe>
        </div>
        @endif
    </div>
</x-filament-panels::page>