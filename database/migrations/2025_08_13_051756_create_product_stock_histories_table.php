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
        Schema::create('product_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamp('tanggal')->useCurrent()->index();
            $table->foreignId('product_stock_id')
                ->constrained('product_stocks')
                ->onDelete('cascade');
            $table->integer('qty_awal')->default(0);
            $table->integer('qty_akhir')->default(0);
            $table->integer('stock_akhir')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('total_biaya_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->enum('jenis', ['barang masuk', 'barang keluar', 'retur customer', 'retur supplier', 'stock awal', 'pemulihan stock', 'batal barang masuk', 'batal barang keluar'])->default('barang masuk')->index();
            $table->string('keterangan')->nullable();
            $table->string('no_transaksi')->nullable()->index();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // masuk, keluar, retur
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_histories');
    }
};
