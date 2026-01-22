<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penjualan_barangs', function (Blueprint $table) {
            $table->decimal('penjualan_barang_jumlahpayment')
                ->default(0)
                ->after('penjualan_barang_grandtotal')
                ->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_barangs', function (Blueprint $table) {
            $table->dropColumn('penjualan_barang_jumlahpayment');
            //
        });
    }
};
