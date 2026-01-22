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
        Schema::create('penjualan_barang_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_barang_id')
                ->constrained('penjualan_barangs')
                ->onDelete('cascade');
            $table->foreignId('product_stock_id')
                ->constrained('product_stocks')
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');
            $table->string('penjualan_barang_detailproduct_name');
            $table->string('penjualan_barang_detail_sn')->nullable()->index();
            $table->date('penjualan_barang_detail_ed')->nullable()->index();
            $table->integer('penjualan_barang_detail_qty')
                ->default(0)->index();
            $table->decimal('penjualan_barang_detail_price', 10, 2)
                ->default(0)->index();
            $table->decimal('penjualan_barang_detail_discount', 10, 2)
                ->default(0);
            $table->enum('penjualan_barang_detail_discounttype', ['persen', 'rupiah'])
                ->default('persen');
            $table->decimal('penjualan_barang_detail_total', 10, 2)
                ->default(0);
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'created_at']);
            $table->index('penjualan_barang_detailproduct_name', 'penjualan_product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_barang_details');
    }
};
