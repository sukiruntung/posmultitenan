async function getPrinter() {
    try {
        const device = await navigator.bluetooth.requestDevice({
            filters: [
                { namePrefix: "RPP" },
                { namePrefix: "Thermal" },
                { namePrefix: "POS" },
            ],
            optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"],
        });
        
        // Save printer info to localStorage
        if (device && device.id) {
            localStorage.setItem('printer-id', device.id);
            localStorage.setItem('printer-name', device.name);
            localStorage.setItem('printer-connected', 'true');
            console.log('Printer saved:', device.name, device.id);
        }
        
        return device;
    } catch (e) {
        console.error("Failed to connect printer", e);
    }
}

function checkSavedPrinter() {
    const printerId = localStorage.getItem('printer-id');
    console.log('Saved Printer ID:', printerId);
    return printerId;
}

async function printThermal(text) {
    try {
        // Cek localStorage untuk printer
        const printerConnected = localStorage.getItem('printer-connected');
        if (!printerConnected) {
            throw new Error("Printer belum disambungkan. Silakan klik tombol 'Sambungkan Printer' terlebih dahulu.");
        }

        // Jika window.connectedPrinter tidak ada, coba request ulang
        if (!window.connectedPrinter) {
            console.log('Reconnecting to saved printer...');
            window.connectedPrinter = await navigator.bluetooth.requestDevice({
                filters: [
                    { namePrefix: "RPP" },
                    { namePrefix: "Thermal" },
                    { namePrefix: "POS" },
                ],
                optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"],
            });
        }

        // Pastikan printer terhubung
        if (!window.connectedPrinter.gatt.connected) {
            console.log('Connecting to printer...');
            await window.connectedPrinter.gatt.connect();
        }

        const server = window.connectedPrinter.gatt;
        const service = await server.getPrimaryService(
            "000018f0-0000-1000-8000-00805f9b34fb"
        );

        const characteristic = await service.getCharacteristic(
            "00002af1-0000-1000-8000-00805f9b34fb"
        );

        const encoder = new TextEncoder();
        const data = encoder.encode(text);

        await characteristic.writeValue(data);

        console.log("Success print", text);
        alert('Berhasil mencetak ke printer thermal');
    } catch (e) {
        console.error("Failed to print thermal", e);
        
        // Reset printer connection on any GATT error
        window.connectedPrinter = null;
        localStorage.removeItem('printer-connected');
        
        if (e.message.includes('GATT') || e.name === 'NotSupportedError') {
            alert('Koneksi printer bermasalah. Klik "Reset Printer" lalu "Sambungkan Printer" untuk menyambung ulang.');
        } else if (e.message.includes('GATT Server is disconnected')) {
            alert('Printer terputus. Silakan klik tombol "Sambungkan Printer" untuk menyambung ulang.');
        } else {
            alert('Gagal mencetak: ' + e.message);
        }
    }
}
