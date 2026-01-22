
    @vite('resources/css/app.css')
<h1>Grand Total : Rp {{ number_format($record->penjualan_barang_grandtotal, 0, ',', '.') }}</h1>

<table class="min-w-full border mt-4">
    <thead class="bg-gray-50 text-gray-700">
        <tr>
            <th class="border px-2 py-1">Tanggal</th>
            <th class="border px-2 py-1">Metode</th>
            <th class="border px-2 py-1">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($record->paymentPenjualan as $payment)
            <tr>
                <td class="border px-2 py-1">{{ $payment->created_at->format('d-m-Y') }}</td>
                <td class="border px-2 py-1">{{ $payment->payment_penjualan_metode }}</td>
                <td class="border px-2 py-1 text-right">Rp {{ number_format($payment->payment_penjualan_jumlah, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>