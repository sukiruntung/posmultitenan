<?php

namespace Database\Seeders;

use App\Models\Accounting\KategoriPengeluaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriPengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KategoriPengeluaran::insert([
            'outlet_id' => 1,
            'kategori_pengeluaran_name' => 'Payment Supplier',
            'user_id' => 1,
        ]);
    }
}
