<?php

namespace App\Exports;

use App\Models\Penjualan\PenjualanBarang;
use App\Models\Penjualan\PenjualanBarangDetail;
use Maatwebsite\Excel\Concerns\FromCollection;

class LaporanPenjualanPivotExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = PenjualanBarangDetail::with(['penjualanBarang.customer', 'product'])
            ->get();
        // dd($data);

        // Ambil produk unik
        $products = $data->pluck('product.product_name')->unique()->values();

        // Header
        $header = collect(['No Invoice', 'Customer'])->merge($products);

        // Group per transaksi
        $rows = $data->groupBy('penjualan_barang_id')->map(function ($items) use ($products) {
            $row = [
                $items->first()->penjualanBarang->penjualan_barang_no,
                $items->first()->penjualanBarang->customer->customer_name ?? '-',
            ];

            foreach ($products as $product) {
                $row[] = optional(
                    $items->firstWhere('product.product_name', $product)
                )->penjualan_barang_detail_qty ?? 0;
            }

            return $row;
        });
        return collect([$header])->merge($rows->values());
    }
}
