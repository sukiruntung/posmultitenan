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
        Schema::create('payment_penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_barang_id')
                ->constrained('penjualan_barangs')
                ->onDelete('cascade');
            $table->date('payment_penjualan_tanggal')->index();
            $table->enum('payment_penjualan_metode', ['cash', 'transfer', 'debit/EDC', 'qris', 'ewallet', 'giro/cek',])->default('cash')->index();
            $table->enum('payment_penjualan_status', ['Belum Lunas', 'Lunas', 'Tidak Terbayar'])->default('Lunas')->index();
            $table->decimal('payment_penjualan_jumlah', 10, 2)->default(0)->index();
            $table->decimal('payment_penjualan_grandtotal', 10, 2)->default(0)->index();
            $table->string('payment_penjualan_bankname', 100)->nullable()->index();
            $table->string('payment_penjualan_accountnumber', 50)->nullable();
            $table->string('payment_penjualan_approvalcode', 50)->nullable()->comment('EDC/Transfer/Debit');
            $table->string('payment_penjualan_referenceid', 50)->nullable()->comment('QRIS/E-Wallet');
            $table->string('payment_penjualan_checkquenumber', 50)->nullable()->comment('GIRO/CEK');
            $table->date('payment_penjualan_jatuhtempo')->nullable()->comment('GIRO/CEK');
            $table->string('payment_penjualan_notes')->nullable();
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
        Schema::dropIfExists('payment_penjualans');
    }
};
