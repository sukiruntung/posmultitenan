<?php

namespace Database\Seeders;

use App\Models\Accesses\SystemDashboard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemDashboard::insert([[
            'id' => 1,
            'system_dashboardname' => 'Omset',
            'user_id' => 1
        ], [
            'id' => 2,
            'system_dashboardname' => 'Laporan Penjualan per Bulan',
            'user_id' => 1
        ], [
            'id' => 3,
            'system_dashboardname' => 'Laporan Perbandingan Omset Marketing Per Bulan',
            'user_id' => 1
        ], [
            'id' => 4,
            'system_dashboardname' => 'Laporan Produk dibawah Stok Minimal',
            'user_id' => 1
        ], [
            'id' => 5,
            'system_dashboardname' => 'Daftar Produk ED / Kurang dari 1 Bulan',
            'user_id' => 1
        ]]);
    }
}
