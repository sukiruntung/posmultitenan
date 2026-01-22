@vite('resources/css/app.css')
<div class="dark:bg-gray-900 dark:text-gray-100 p-2 sm:p-4">

    <h1 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">
       Product : {{ $product->product->product_name }}
        <span class="block text-sm font-normal text-gray-500 dark:text-gray-400">
            SN: {{ $product->product_stock_sn }}
        </span>
    </h1>

    {{-- WRAPPER AGAR RESPONSIVE DI HP --}}
    <div class="overflow-x-auto rounded-md border border-gray-300 dark:border-gray-700">
        <table class="min-w-full text-sm sm:text-base">
            <thead class="bg-primary-600 dark:bg-primary-700 text-gray-100 text-xs sm:text-sm">
                <tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Tanggal</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Before</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">After</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">+/-</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Ket</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($histories as $history)
                    @php
                        $diff = $history->stock_akhir - $history->stock_awal;
                    @endphp

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ $history->created_at }}
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-200 text-center">
                            {{ $history->stock_awal }}
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-200 text-center">
                            {{ $history->stock_akhir }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $diff > 0 
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                    : ($diff < 0 
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300')
                                }}">
                                {{ $diff > 0 ? '+' : ($diff < 0 ? '-' : '') }}{{ abs($diff) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-[250px] break-words">
                            {{ $history->no_transaksi. ' ('.$history->jenis.') '. $history->keterangan }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($histories->isEmpty())
        <p class="text-center text-gray-600 dark:text-gray-400 mt-4">Tidak ada history.</p>
    @endif

</div>
