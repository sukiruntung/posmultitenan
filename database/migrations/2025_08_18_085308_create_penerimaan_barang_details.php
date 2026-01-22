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
        Schema::create('penerimaan_barang_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_barang_id')
                ->constrained('penerimaan_barangs')
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');
            $table->string('penerimaan_barang_detailproduct_name');
            $table->string('penerimaan_barang_detail_sn')->nullable()->index();
            $table->date('penerimaan_barang_detail_ed')->nullable()->index();
            $table->integer('penerimaan_barang_detail_qty')->index()
                ->default(0);
            $table->decimal('penerimaan_barang_detail_price', 10, 2)
                ->default(0);
            $table->decimal('penerimaan_barang_detail_discount', 10, 2)
                ->default(0);
            $table->decimal('penerimaan_barang_detail_total', 10, 2)
                ->default(0);
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'created_at']);
            $table->index('penerimaan_barang_detailproduct_name', 'product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang_details');
    }
};
