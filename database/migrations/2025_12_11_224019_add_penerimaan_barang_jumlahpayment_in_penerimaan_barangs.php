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
        Schema::table('penerimaan_barangs', function (Blueprint $table) {
            $table->decimal('penerimaan_barang_jumlahpayment', 10, 2)->default(0)
                ->after('penerimaan_barang_grandtotal')->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barangs', function (Blueprint $table) {
            $table->dropColumn('penerimaan_barang_jumlahpayment');
        });
    }
};
