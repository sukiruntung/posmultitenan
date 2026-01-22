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
        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')
                ->constrained('outlets')
                ->onDelete('cascade');
            $table->dateTime('penerimaan_barang_tanggal')->index();
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->onDelete('cascade')
                ->comment('ID pemasok');
            $table->string('penerimaan_barang_invoicenumber');
            $table->decimal('penerimaan_barang_total', 10, 2)->index();
            $table->decimal('penerimaan_barang_discount', 10, 2)
                ->default(0);
            $table->decimal('penerimaan_barang_ppn', 10, 2)
                ->default(0);
            $table->decimal('penerimaan_barang_grandtotal', 10, 2)->index();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'created_at']);
            $table->unique(
                ['penerimaan_barang_invoicenumber', 'outlet_id'],
                'uniq_invoice_outlet'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barangs');
    }
};
