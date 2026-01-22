
<x-filament-panels::page>
    @vite('resources/css/app.css')
    <div class="space-y-6">

        <!-- Header -->
       <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Detail Kas Harian
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tanggal {{ $this->record->kas_harian_tanggalbuka }}
                </p>
            </div>
            
        </div>

         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-filament::card>
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Saldo Awal</p>
                        <p class="text-lg font-semibold">Rp {{ number_format($this->record->kas_harian_saldoawal) }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pemasukkan</p>
                        <p class="text-lg font-semibold text-green-600">Rp {{ number_format($this->getTotalPemasukkan()) }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pengeluaran</p>
                        <p class="text-lg font-semibold text-red-600">Rp {{ number_format($this->getTotalPengeluaran()) }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Saldo Seharusnya</p>
                        <p class="text-lg font-semibold text-blue-600">Rp {{ number_format($this->record->kas_harian_saldoseharusnya) }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Selisih</p>
                        <p class="text-lg font-semibold {{ $this->record->kas_harian_selisih >= 0? 'text-yellow-600' : 'text-red-600' }}">Rp {{ number_format($this->record->kas_harian_selisih) }}</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        <x-filament::section
            collapsible
            collapsed
        >
            <x-slot name="heading">
                Rincian Kas Pemasukkan
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

    </div>
</x-filament-panels::page>
