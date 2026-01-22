<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ReportController extends Controller
{
    public function penjualan()
    {
        // $footerHtml = view('reports.partials.footer')->render();

        // // buat file temp dengan ekstensi .html
        // $footerPath = tempnam(sys_get_temp_dir(), 'footer_') . '.html';
        // file_put_contents($footerPath, $footerHtml);
        $pdf = PDF::loadView('reports.test-cobaheaderfooter')
            ->setPaper('a4')
            ->setOption('encoding', 'UTF-8')
            ->setOption('margin-top', 30)
            ->setOption('margin-bottom', 70)
            ->setOption('header-html', resource_path('views/reports/html/header.html'))
            ->setOption('footer-html',  resource_path('views/reports/html/footer.html'))
            ->setOption('footer-center', 'Halaman [page] dari [toPage]');

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan.pdf"',
        ]);
    }
}
