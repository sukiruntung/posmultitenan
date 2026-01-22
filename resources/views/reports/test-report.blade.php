<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Laporan Penjualan Barang</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Transaksi</th>
                <th>Customer</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
             <tr>
                <th>1</th>
                <th>2025-10-01</th>
                <th>F87374784</th>
                <th>Customer Test</th>
                <th>20000</th>
             </tr>
            {{-- @foreach ($data as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->penjualan_barang_tanggal }}</td>
                <td>{{ $row->penjualan_barang_no }}</td>
                <td>{{ $row->customer->name ?? '-' }}</td>
                <td style="text-align:right">{{ number_format($row->penjualan_barang_total, 0, ',', '.') }}</td>
            </tr>
            @endforeach --}}
        </tbody>
    </table>
</body>
</html>
