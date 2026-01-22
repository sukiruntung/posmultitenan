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
        Schema::table('product_stock_histories', function (Blueprint $table) {

            $table->renameColumn('qty_awal', 'qty_masuk');
            $table->renameColumn('qty_akhir', 'qty_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stock_histories', function (Blueprint $table) {

            $table->renameColumn('qty_awal', 'qty_masuk');
            $table->renameColumn('qty_akhir', 'qty_keluar');
            //
        });
    }
};
