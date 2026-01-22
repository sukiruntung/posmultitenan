
<x-filament-panels::page>
     
    <div class="space-y-4">
        <x-filament::button color="primary" wire:click="connectPrinter">
            Sambungkan Printer
        </x-filament::button>
        
        <x-filament::button color="gray" id="check-printer-btn">
            Cek Printer Tersimpan
        </x-filament::button>
    </div>
        
</x-filament-panels::page>

@include('filament.layouts.app')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkBtn = document.getElementById('check-printer-btn');
    if (checkBtn) {
        checkBtn.addEventListener('click', function() {
            // Check saved printer from localStorage
            const printerConnected = localStorage.getItem('printer-connected');
            const printerName = localStorage.getItem('printer-name');
            const printerId = localStorage.getItem('printer-id');
            
            if (printerConnected && printerName) {
                alert(`Printer tersimpan: ${printerName} (ID: ${printerId})`);
            } else {
                alert('Tidak ada printer yang tersimpan');
            }
        });
    }
});
</script>
@endpush


{{-- @push('scripts')
    {{-- <script src="{{ asset('js/thermal-printer.js') }}"></script> --}}
    {{-- <script>
        document.addEventListener('livewire:initialized', () => {
            const connectButton = document.getElementById('connect-button');

            connectButton.addEventListener('click', async () => {
                window.connectedPrinter = await getPrinter()
            })

        })
    </script>
@endpush  --}}
