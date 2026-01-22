<?php

namespace Database\Seeders;

use App\Models\Accesses\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::insert([
            [
                'id' => 1,
                'menu_name' => 'Master Data',
                'menu_link' => 'products/kelompok-products',
                'user_id' => 1,
            ],
            [
                'id' => 2,
                'menu_name' => 'Penerimaan Barang',
                'menu_link' => 'pembelian/penerimaan-barangs',
                'user_id' => 1,
            ],
            [
                'id' => 3,
                'menu_name' => 'Surat Jalan / Faktur',
                'menu_link' => 'penjualan/penjualan-barangs',
                'user_id' => 1,
            ],
            [
                'id' => 4,
                'menu_name' => 'Payment Penjualan',
                'menu_link' => 'penjualan/payment-penjualans',
                'user_id' => 1,
            ],
            [
                'id' => 5,
                'menu_name' => 'Transaksi Payment',
                'menu_link' => 'kasir/payment-kasirs',
                'user_id' => 1,
            ],
            [
                'id' => 6,
                'menu_name' => 'Pengeluaran Lain-Lain',
                'menu_link' => 'pembelian/kas-pengeluarans',
                'user_id' => 1,
            ],
            [
                'id' => 7,
                'menu_name' => 'History Product Stock',
                'menu_link' => 'products/history-product-stock',
                'user_id' => 1,
            ],
            [
                'id' => 8,
                'menu_name' => 'Laporan lain-lain',
                'menu_link' => 'laporan/laporan',
                'user_id' => 1,
            ],
            [
                'id' => 9,
                'menu_name' => 'Payment Supplier',
                'menu_link' => 'pembelian/payment-penerimaans',
                'user_id' => 1,
            ],
            [
                'id' => 10,
                'menu_name' => 'Kasir',
                'menu_link' => 'kasir/buka-kasirs',
                'user_id' => 1,
            ]]

        );
    }
}
