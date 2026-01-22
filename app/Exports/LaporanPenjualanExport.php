<?php

namespace App\Exports;

use App\Models\Penjualan\PenjualanBarang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanPenjualanExport implements FromQuery, WithMapping, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        return PenjualanBarang::with('customer');
    }
    // public function collection()
    // {
    //     return PenjualanBarang::with('customer')->get();
    // }
    public function headings(): array
    {
        return [
            'Tanggal',
            'No Invoice',
            'Customer',
            'Total'
        ];
    }
    public function map($row): array
    {
        return [
            $row->penjualan_barang_tanggal,
            $row->penjualan_barang_no,
            $row->customer?->customer_name ?? '-',
            $row->penjualan_barang_grandtotal,
        ];
    }
}
