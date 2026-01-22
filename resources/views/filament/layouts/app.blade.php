<script src="{{ asset('js/thermal-printer.js') }}"></script>
<script>
document.addEventListener('livewire:initialized', () => {
    console.log('FILAMENT JS AKTIF');
    
    Livewire.on('printer-connect', async () => {
        try {
            window.connectedPrinter = await getPrinter();
            alert('Printer tersambung: ' + window.connectedPrinter.name);
        } catch (e) {
            console.error('Error connecting printer:', e);
            alert('Gagal menghubungkan printer: ' + e);
        }
    });
    
    Livewire.on('printer-reset', () => {
        // Reset printer connection
        window.connectedPrinter = null;
        localStorage.removeItem('printer-connected');
        localStorage.removeItem('printer-name');
        localStorage.removeItem('printer-id');
        alert('Printer direset. Silakan sambungkan ulang.');
    });

    Livewire.on('printer-print', async (text) => {
        try {
            await printThermal(text);
        } catch (e) {
            alert(e);
        }
    });

});
</script>
