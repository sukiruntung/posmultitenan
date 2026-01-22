<?php

namespace Database\Seeders;

use App\Models\Counters\DocumentNumbering;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentNumberingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentNumbering::insert([
            'outlet_id' => 1,
            'document_numbering_name' => 'penjualan_barang',
            'document_numbering_prefix' => 'SJ',
            'document_numbering_format' => '[document_numbering_prefix] - [Y][M]',
            'document_numbering_numberlength' => 4,
            'document_numbering_currentnumber' => 1,
            'document_numbering_resettype' => 'yearly',
            'user_id' => 1
        ]);
    }
}
