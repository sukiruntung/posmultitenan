console.log('Loading printer.js...');

window.Printer = {
    device: null,
    characteristic: null,

    async connect() {
        console.log("fghggh");
        this.device = await navigator.bluetooth.requestDevice({
            filters: [
                { namePrefix: "RPP" },
                { namePrefix: "POS" },
                { namePrefix: "Thermal" },
            ],
            optionalServices: ["000018f0-0000-1000-8000-00805f9b34fb"],
        });
        const server = await this.device.gatt.connect();
        const service = await server.getPrimaryService(
            "000018f0-0000-1000-8000-00805f9b34fb"
        );
        this.characteristic = await service.getCharacteristic(
            "00002af1-0000-1000-8000-00805f9b34fb"
        );

        localStorage.setItem("printer-id", this.device.id);
        console.log("Printer connected");
    },

    async reconnect() {
        const devices = await navigator.bluetooth.getDevices();
        const savedId = localStorage.getItem("printer-id");

        const device = devices.find((d) => d.id === savedId);
        if (!device) throw "Printer belum disambungkan";

        this.device = device;

        if (!device.gatt.connected) {
            const server = await device.gatt.connect();
            const service = await server.getPrimaryService(
                "000018f0-0000-1000-8000-00805f9b34fb"
            );
            this.characteristic = await service.getCharacteristic(
                "00002af1-0000-1000-8000-00805f9b34fb"
            );
        }
    },

    async print(text) {
        if (!this.characteristic) {
            await this.reconnect();
        }

        const encoder = new TextEncoder();
        await this.characteristic.writeValue(encoder.encode(text + "\n\n"));
    },

    checkSavedPrinter() {
        const printerId = localStorage.getItem("printer-id");
        console.log("Saved Printer ID:", printerId);
        return printerId;
    },
};

console.log('window.Printer loaded:', window.Printer);
