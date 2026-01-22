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
        Schema::table('customer_products', function (Blueprint $table) {
            $table->decimal('harga_jual', 10, 2)->nullable()->after('first_product_stock_id');
            $table->integer('frekuensi')->after('harga_jual')->default(0)->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn('harga_jual');
            $table->dropColumn('frekuensi');
            //
        });
    }
};
