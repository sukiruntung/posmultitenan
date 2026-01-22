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
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->decimal('harga_beli', 10, 2)
                ->after('first_product_stock_id')
                ->default(0);
            $table->integer('frekuensi')->after('harga_beli')->default(0)->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
            $table->dropColumn('frekuensi');
            //
        });
    }
};
