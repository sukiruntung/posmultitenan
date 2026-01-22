<?php

namespace App\Exports;

use App\Models\Products\ProductStock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanProductStockExport implements FromQuery, WithMapping, WithHeadings, WithEvents, WithCustomStartCell
{
    public function __construct(public $query) {}

    public function startCell(): string
    {
        return 'A3';
    }
    public function query()
    {
        return $this->query;
        // return ProductStock::with('product.satuan');
    }
    public function headings(): array
    {
        return [
            'Nama Product',
            'Stock',
            'Satuan'
        ];
    }
    public function map($row): array
    {
        return [
            $row->product->product_name,
            $row->stock,
            $row->product->satuan->satuan_name,
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul
                $sheet->setCellValue('A1', 'LAPORAN STOCK PRODUK');

                // Merge judul
                $sheet->mergeCells('A1:C1');

                // Style judul
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            },
        ];
    }
}
