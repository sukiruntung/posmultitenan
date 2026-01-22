<div class="flex items-center justify-between">
    <h1 class="text-xl font-bold">{{ $title }}</h1>

    <div class="flex gap-2">
        @foreach ($actions as $action)
            {{ $action }}
        @endforeach
    </div>
</div>
<x-filament::section>
    @livewire(\App\Filament\Resources\PaymentPenjualanResource\Widgets\PaymentTabs::class, [
        'active' => $active,
        'module' => $module,
    ])
</x-filament::section>
