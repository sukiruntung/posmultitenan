<div class="flex items-center gap-2">
    @if ($logo)
        <img
            src="{{ $logo }}"
            alt="{{ $name }}"
            class="h-8 w-auto"
        >
    @endif

    @if ($name)
        <span class="text-lg font-bold">
            {{ $name }}
        </span>
    @endif
</div>
