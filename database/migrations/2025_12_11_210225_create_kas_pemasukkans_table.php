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
        Schema::create('kas_pemasukkans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kas_harian_id')->constrained('kas_harians')->onDelete('cascade');
            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->enum('kas_pemasukkan_jenis', ['masuk', 'keluar'])->index();
            $table->decimal('kas_pemasukkan_jumlah', 10, 2)->default(0)->index();
            $table->string('kas_pemasukkan_sumber')->nullable();
            $table->integer('kas_pemasukkan_reference')->nullable()->index();
            $table->string('kas_pemasukkan_notransaksi', 50)->index();
            $table->text('kas_pemasukkan_notes')->nullable();
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
        Schema::dropIfExists('kas_pemasukkans');
    }
};
