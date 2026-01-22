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
        Schema::create('penjualan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->dateTime('penjualan_barang_tanggal')->index();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('ID pemasok');
            $table->string('penjualan_barang_no')->unique();
            $table->decimal('penjualan_barang_total', 10, 2)->index();
            $table->decimal('penjualan_barang_discount', 10, 2)
                ->default(0);
            $table->decimal('penjualan_barang_ongkir', 10, 2)
                ->default(0);
            $table->enum('penjualan_barang_discounttype', ['percent', 'rupiah'])->default('percent');
            $table->decimal('penjualan_barang_ppn', 10, 2)
                ->default(0);
            $table->decimal('penjualan_barang_grandtotal', 10, 2)->index();
            $table->text('notes')->nullable();
            $table->integer('penjualan_barang_validatedby')->defalut(0);
            $table->dateTime('penjualan_barang_validatedat')->nullable()->index();
            $table->enum('penjualan_barang_status', ['pending', 'validated', 'belum lunas', 'lunas'])->default('pending')->index();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('penjualan_barangs');
    }
};
