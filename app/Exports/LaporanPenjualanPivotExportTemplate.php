<?php

namespace App\Exports;

use App\Models\Penjualan\PenjualanBarang;
use App\Models\Penjualan\PenjualanBarangDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LaporanPenjualanPivotExportTemplate implements
    FromCollection,
    WithCustomStartCell,
    WithEvents
{
    public function __construct(
        protected string $tanggalAwal,
        protected string $tanggalAkhir
    ) {}
    protected array $productSnMap = [];

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        $data = PenjualanBarangDetail::with([
            'penjualanBarang.customer',
            'product'
        ])->whereHas('penjualanBarang', function ($q) {
            $q->whereBetween('penjualan_barang_tanggal', [
                $this->tanggalAwal,
                $this->tanggalAkhir
            ]);
        })->get();

        $this->productSnMap = $data
            ->groupBy('product.product_name')
            ->map(
                fn($items) =>
                $items->pluck('penjualan_barang_detail_sn')->unique()->values()
            )
            ->toArray();
        $no = 1;
        return $data->groupBy('penjualan_barang_id')->map(function ($items) use (&$no) {
            $row = [
                $no++,
                $items->first()->penjualanBarang->penjualan_barang_no,
                $items->first()->penjualanBarang->customer->customer_name ?? '-',
                '',
            ];

            foreach ($this->productSnMap as $product => $sns) {
                foreach ($sns as $sn) {
                    $row[] = $items
                        ->where('product.product_name', $product)
                        ->where('penjualan_barang_detail_sn', $sn)
                        ->sum('penjualan_barang_detail_qty') ?: '';
                }
            }

            return $row;
        })->values();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Judul
                $sheet->setCellValue('A1', 'LAPORAN PENJUALAN DETAIL');
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A2', "PERIODE : {$this->tanggalAwal} - {$this->tanggalAkhir}");
                $sheet->mergeCells('A2:D2');

                // Header kiri
                $sheet->setCellValue('A4', 'NO');
                $sheet->setCellValue('B4', 'NO FAKTUR');
                $sheet->setCellValue('C4', 'CUSTOMER');
                $sheet->setCellValue('D4', 'NAMA PRODUCT');
                $sheet->setCellValue('D5', 'SN');
                $sheet->mergeCells('A4:A5');
                $sheet->mergeCells('B4:B5');
                $sheet->mergeCells('C4:C5');

                // Header product + SN
                $colIndex = 5;

                foreach ($this->productSnMap as $product => $sns) {

                    $start = $colIndex;
                    $end   = $colIndex + count($sns) - 1;

                    $sheet->setCellValueByColumnAndRow($start, 4, $product);
                    $sheet->mergeCellsByColumnAndRow($start, 4, $end, 4);

                    foreach ($sns as $sn) {
                        $sheet->setCellValueByColumnAndRow($colIndex, 5, $sn);
                        $colIndex++;
                    }
                }

                $sheet->getStyle("A1:{$sheet->getHighestColumn()}5")
                    ->getFont()->setBold(true);

                $sheet->getStyle("A1:{$sheet->getHighestColumn()}5")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');
            }
        ];
    }
}
