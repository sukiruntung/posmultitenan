<?php

namespace App\Exports;

use App\Models\Penjualan\PenjualanBarang;
use App\Models\Penjualan\PenjualanBarangDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanPenjualanPivotExport2Header implements FromCollection, WithEvents,    WithCustomStartCell
{
    protected array $productSnMap = [];
    public function startCell(): string
    {
        return 'A3'; // data mulai baris 3
    }

    public function collection()
    {
        $data = PenjualanBarangDetail::with(['penjualanBarang.customer', 'product'])
            ->get();

        /**
         * Bentuk:
         * [
         *   'Laptop Asus' => ['SN A', 'SN B'],
         *   'Mouse Logitech' => ['SN X', 'SN Y']
         * ]
         */
        $this->productSnMap = $data
            ->groupBy('product.product_name')
            ->map(
                fn($items) =>
                $items->pluck('penjualan_barang_detail_sn')->unique()->values()
            )
            ->toArray();

        // Pivot data per transaksi
        $rows = $data->groupBy('penjualan_barang_id')->map(function ($items) {

            $row = [
                $items->first()->penjualanBarang->penjualan_barang_no,
                $items->first()->penjualanBarang->customer->customer_name ?? '-',
            ];

            foreach ($this->productSnMap as $product => $sns) {
                foreach ($sns as $sn) {
                    $qty = $items
                        ->where('product.product_name', $product)
                        ->where('penjualan_barang_detail_sn', $sn)
                        ->sum('penjualan_barang_detail_qty');

                    $row[] = $qty ?: 0;
                }
            }

            return $row;
        });

        return $rows->values();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // HEADER KIRI
                $sheet->setCellValue('A2', 'No Invoice');
                $sheet->setCellValue('B2', 'Customer');

                // $sheet->mergeCells('A1:A2');
                // $sheet->mergeCells('B1:B2');

                // HEADER PRODUK & SN
                $colIndex = 3;

                foreach ($this->productSnMap as $product => $sns) {

                    $startCol = $colIndex;
                    $endCol   = $colIndex + count($sns) - 1;

                    $sheet->setCellValueByColumnAndRow($startCol, 1, $product);
                    $sheet->mergeCellsByColumnAndRow($startCol, 1, $endCol, 1);

                    foreach ($sns as $sn) {
                        $sheet->setCellValueByColumnAndRow($colIndex, 2, $sn);
                        $colIndex++;
                    }
                }

                // STYLE
                $sheet->getStyleByColumnAndRow(1, 1, $colIndex - 1, 2)
                    ->getFont()->setBold(true);

                $sheet->getStyleByColumnAndRow(1, 1, $colIndex - 1, 2)
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');
            }
        ];
    }
}
