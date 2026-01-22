document.addEventListener("livewire:initialized", () => {
    Livewire.on("print-start", async (text) => {
        console.log("Menerima event print-start", text);
        await printThermal(text);
    });
});
