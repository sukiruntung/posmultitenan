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
        Schema::create('kas_pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->date('kas_pengeluaran_tanggal')->index();
            $table->foreignId('kas_harian_id')->constrained('kas_harians')->onDelete('cascade');
            $table->foreignId('kasir_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('kategori_pengeluaran_id')
                ->constrained('kategori_pengeluarans')
                ->onDelete('cascade');
            $table->text('kas_pengeluaran_notes')->nullable();
            $table->decimal('kas_pengeluaran_jumlah', 10, 2)->default(0)->index();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_pengeluarans');
    }
};
