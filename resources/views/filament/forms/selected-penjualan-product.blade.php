@vite('resources/css/app.css')

@if (!empty($this->products))
<div class="text-gray-600 font-semibold mb-2">
    <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
        Detail product
    </h1>
</div>
{{-- MOBILE CARD VIEW --}}
@include('livewire.penjualan.list-table-mobile')


{{-- Desktop CARD VIEW --}}
@include('livewire.penjualan.list-table-desktop')

@else
<div class="text-gray-500 italic">Belum ada produk dipilih.</div>
@endif

<div class="errorQty">

</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
