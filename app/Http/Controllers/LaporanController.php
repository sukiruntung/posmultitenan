<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPenjualanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // ✅ BENAR

// use Maatwebsite\Excel\Excel;

class LaporanController extends Controller
{

    public function exportExcel()
    {
        return Excel::download(
            new LaporanPenjualanExport,
            'laporan-penjualan.xlsx'
        );
    }
}
